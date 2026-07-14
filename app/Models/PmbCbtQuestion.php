<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbCbtQuestion extends Model
{
    protected $fillable = [
        'category',
        'question',
        'options',
        'correct_option',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
