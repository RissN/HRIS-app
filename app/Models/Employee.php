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
        'bank_name',
        'account_number',
        'joined_date',
        'avatar',
    ];

    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'joined_date' => 'date',
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
