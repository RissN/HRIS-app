<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDailyScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'attendance_status',
        'check_in_time',
        'late_minutes',
        'attendance_score',
        'appreciation_score',
        'total_score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'late_minutes' => 'integer',
            'attendance_score' => 'integer',
            'appreciation_score' => 'integer',
            'total_score' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
