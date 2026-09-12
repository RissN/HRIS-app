<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'daily_allowance',
        'late_deduction',
        'absent_deduction',
        'net_salary',
        'status',
        'payment_date',
        'note',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'daily_allowance' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
