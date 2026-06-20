<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PmbPeriod extends Model
{
    protected $fillable = [
        'sevima_id',
        'name',
        'short_name',
        'academic_year',
        'starts_at',
        'ends_at',
        'midterm_starts_at',
        'midterm_ends_at',
        'final_starts_at',
        'final_ends_at',
        'is_active',
        'brochure_path',
        'raw_payload',
    ];

    protected $appends = [
        'brochure_url',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'midterm_starts_at' => 'date',
            'midterm_ends_at' => 'date',
            'final_starts_at' => 'date',
            'final_ends_at' => 'date',
            'is_active' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function getBrochureUrlAttribute(): ?string
    {
        if (! $this->brochure_path) {
            return null;
        }

        return Storage::disk('public')->url($this->brochure_path);
    }
}
