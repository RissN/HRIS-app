<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'position',
        'department',
        'annual_leave_quota',
        'bank_name',
        'account_number',
        'joined_date',
        'avatar',
    ];

    protected function casts(): array
    {
        return [
            'annual_leave_quota' => 'integer',
            'account_number' => 'encrypted',
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
