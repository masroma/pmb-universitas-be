<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbRegistrationPath extends Model
{
    protected $fillable = [
        'sevima_id',
        'title',
        'period',
        'fee',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'raw_payload' => 'array',
        ];
    }
}
