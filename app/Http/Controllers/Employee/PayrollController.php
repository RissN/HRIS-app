<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Employee/Payroll/Index', [
            'payrolls' => $payrolls,
            'employee' => $employee->load('user'),
        ]);
    }
}
