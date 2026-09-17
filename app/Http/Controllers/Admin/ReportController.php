<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $department = $request->input('department', 'all');
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = $this->buildFilteredQuery($month, $department, $status, $search);

        // Calculate summary statistics
        $statsQuery = clone $query;
        $allMatching = $statsQuery->get();

        $stats = [
            'total' => $allMatching->count(),
            'present' => $allMatching->where('status', 'present')->count(),
            'late' => $allMatching->where('status', 'late')->count(),
            'sick' => $allMatching->where('status', 'sick')->count(),
            'permission' => $allMatching->where('status', 'permission')->count(),
            'absent' => $allMatching->where('status', 'absent')->count(),
            'wfh' => $allMatching->where('status', 'wfh')->count(),
            'total_late_minutes' => $allMatching->sum('late_minutes'),
        ];

        // Paginated records for table view
        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_at', 'asc')
            ->paginate(15)
            ->withQueryString();

        $departments = Employee::select('department')->distinct()->pluck('department');

        return Inertia::render('Admin/Reports/Index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'departments' => $departments,
            'filters' => [
                'month' => $month,
                'department' => $department,
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $department = $request->input('department', 'all');
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = $this->buildFilteredQuery($month, $department, $status, $search);
        $records = $query->orderBy('date', 'asc')->get();

        $filename = 'rekap-presensi-hris-'.$month.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($file, [
                'No',
                'Tanggal',
                'NIK',
                'Nama Karyawan',
                'Departemen',
                'Jabatan',
                'Jam Masuk',
                'Jam Pulang',
                'Status',
                'Keterlambatan (Menit)',
                'Catatan',
            ]);

            $no = 1;
            foreach ($records as $record) {
                $statusLabel = match ($record->status) {
                    'present' => 'Hadir Tepat Waktu',
                    'late' => 'Terlambat',
                    'sick' => 'Sakit',
                    'permission' => 'Izin',
                    'absent' => 'Alpa / Tanpa Keterangan',
                    'wfh' => 'WFH',
                    default => ucfirst($record->status),
                };

                fputcsv($file, [
                    $no++,
                    $record->date ? Carbon::parse($record->date)->translatedFormat('d-m-Y') : '-',
                    $this->escapeCsvValue($record->employee?->employee_code ?? '-'),
                    $this->escapeCsvValue($record->employee?->user?->name ?? '-'),
                    $this->escapeCsvValue($record->employee?->department ?? '-'),
                    $this->escapeCsvValue($record->employee?->position ?? '-'),
                    $record->check_in_at ? Carbon::parse($record->check_in_at)->format('H:i') : '-',
                    $record->check_out_at ? Carbon::parse($record->check_out_at)->format('H:i') : '-',
                    $statusLabel,
                    $record->late_minutes ?? 0,
                    $this->escapeCsvValue($record->note ?? '-'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildFilteredQuery(string $month, ?string $department, ?string $status, ?string $search)
    {
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $query = Attendance::with(['employee.user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($department && $department !== 'all') {
            $query->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        return $query;
    }

    /**
     * Escape a CSV cell value to prevent formula injection in spreadsheet software.
     */
    private function escapeCsvValue(string $value): string
    {
        if (in_array($value[0] ?? '', ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "\t".$value;
        }

        return $value;
    }
}
