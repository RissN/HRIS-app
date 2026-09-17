<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Employee extends Model
{
    use HasFactory;

    public const REGIONS = [
        'jakarta_timur' => 'Jakarta Timur',
        'jakarta_barat' => 'Jakarta Barat',
        'jakarta_pusat' => 'Jakarta Pusat',
        'jakarta_utara' => 'Jakarta Utara',
        'jakarta_selatan' => 'Jakarta Selatan',
    ];

    public const EMPLOYMENT_STATUSES = [
        'tetap' => 'Karyawan Tetap',
        'vendor' => 'Vendor / Mitra',
        'magang' => 'Karyawan Magang',
    ];

    public const POSITIONS = [
        'Pramudi' => 'Pramudi',
        'Pramusapa' => 'Pramusapa',
        'Pramujaga' => 'Pramujaga',
        'Karyawan Kantor' => 'Karyawan Kantor',
    ];

    public const POOLS = [
        'jakarta_timur' => ['Pool Cawang', 'Pool Pinang Ranti', 'Pool Klender', 'Terminal Kampung Rambutan'],
        'jakarta_barat' => ['Pool Rawa Buaya', 'Pool Pesing', 'Terminal Kalideres'],
        'jakarta_pusat' => ['Kantor Pusat Cawang / Koridor 1', 'Halte Sentral Harmoni', 'Depo Monas'],
        'jakarta_utara' => ['Pool Pegangsaan Dua', 'Pool Tanjung Priok', 'Halte Sentral Pluit'],
        'jakarta_selatan' => ['Pool Cipedak', 'Pool Lebak Bulus', 'Terminal Blok M'],
    ];

    protected $fillable = [
        'user_id',
        'employee_code',
        'phone',
        'position',
        'department',
        'region',
        'employment_status',
        'pool_depot',
        'annual_leave_quota',
        'bank_name',
        'account_number',
        'joined_date',
        'avatar',
    ];

    public static function generateEmployeeCode(string $status): string
    {
        $prefix = match ($status) {
            'magang' => 'TJB',
            'vendor' => 'TJV',
            default => 'TJT',
        };

        $latest = self::where('employee_code', 'like', "{$prefix}%")
            ->orderByDesc('employee_code')
            ->value('employee_code');

        if ($latest && preg_match('/^'.$prefix.'(\d+)$/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = match ($status) {
                'magang' => 1101,
                'vendor' => 2001,
                default => 1001,
            };
        }

        return $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected $appends = [
        'region_label',
        'employment_status_label',
    ];

    public function getRegionLabelAttribute(): string
    {
        return self::REGIONS[$this->region] ?? ucfirst(str_replace('_', ' ', $this->region ?? ''));
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return self::EMPLOYMENT_STATUSES[$this->employment_status] ?? ucfirst($this->employment_status ?? '');
    }

    public function scopeRegion($query, ?string $region)
    {
        if ($region && $region !== 'all') {
            return $query->where('region', $region);
        }

        return $query;
    }

    public function scopeEmploymentStatus($query, ?string $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('employment_status', $status);
        }

        return $query;
    }

    public function scopePosition($query, ?string $position)
    {
        if ($position && $position !== 'all') {
            return $query->where('position', $position);
        }

        return $query;
    }

    public function getNikAttribute(): ?string
    {
        return $this->employee_code;
    }

    public function getAccountNumberAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }

    public function setAccountNumberAttribute(?string $value): void
    {
        $this->attributes['account_number'] = empty($value) ? null : Crypt::encryptString($value);
    }

    protected function casts(): array
    {
        return [
            'annual_leave_quota' => 'integer',
            'joined_date' => 'date',
        ];
    }

    public function getAnnualLeaveUsed(?int $year = null): int
    {
        $year = $year ?? (int) now()->year;

        return (int) $this->leaveRequests()
            ->where('type', 'annual_leave')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_days');
    }

    public function getAnnualLeavePending(?int $year = null): int
    {
        $year = $year ?? (int) now()->year;

        return (int) $this->leaveRequests()
            ->where('type', 'annual_leave')
            ->where('status', 'pending')
            ->whereYear('start_date', $year)
            ->sum('total_days');
    }

    public function getAnnualLeaveRemaining(?int $year = null): int
    {
        $year = $year ?? (int) now()->year;
        $quota = (int) ($this->annual_leave_quota ?? 12);

        return max(0, $quota - $this->getAnnualLeaveUsed($year));
    }

    /**
     * @return array{year: int, quota: int, used: int, pending: int, remaining: int, available: int}
     */
    public function getAnnualLeaveBalance(?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $quota = (int) ($this->annual_leave_quota ?? 12);
        $used = $this->getAnnualLeaveUsed($year);
        $pending = $this->getAnnualLeavePending($year);
        $remaining = max(0, $quota - $used);

        return [
            'year' => $year,
            'quota' => $quota,
            'used' => $used,
            'pending' => $pending,
            'remaining' => $remaining,
            'available' => max(0, $remaining - $pending),
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employeeSchedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function currentSchedule(): ?WorkSchedule
    {
        $employeeSchedule = $this->employeeSchedules()
            ->where('effective_date', '<=', now()->toDateString())
            ->latest('effective_date')
            ->with('schedule')
            ->first();

        return $employeeSchedule?->schedule;
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
