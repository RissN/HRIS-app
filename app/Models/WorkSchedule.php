<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'days',
        'tolerance_minutes',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'array',
            'tolerance_minutes' => 'integer',
        ];
    }

    public function employeeSchedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class, 'schedule_id');
    }
}
