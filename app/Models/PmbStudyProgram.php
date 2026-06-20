<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbStudyProgram extends Model
{
    protected $fillable = [
        'sevima_id',
        'level',
        'title',
        'accreditation',
        'sort_order',
        'is_active',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'raw_payload' => 'array',
        ];
    }
}
