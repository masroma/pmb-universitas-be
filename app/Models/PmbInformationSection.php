<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbInformationSection extends Model
{
    protected $fillable = [
        'program_level',
        'category',
        'title',
        'subtitle',
        'body',
        'items',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
