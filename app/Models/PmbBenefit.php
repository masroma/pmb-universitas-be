<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbBenefit extends Model
{
    protected $fillable = [
        'icon',
        'title',
        'emphasis',
        'description',
        'is_italic',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_italic' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
