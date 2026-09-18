<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAppreciation extends Model
{
    use HasFactory;

    public const SOURCES = [
        'sosmed' => 'Media Sosial Viral (TikTok/X/IG)',
        'customer' => 'Pujian Penumpang / Halte',
        'service' => 'Pelayanan Prima & Keramahan',
        'extra_mile' => 'Inisiatif & Disiplin Ekstra',
    ];

    protected $fillable = [
        'employee_id',
        'admin_id',
        'source',
        'title',
        'description',
        'evidence_url',
        'points',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'points' => 'integer',
        ];
    }

    protected $appends = [
        'source_label',
    ];

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst(str_replace('_', ' ', $this->source));
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
