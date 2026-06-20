<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbSevimaRecord extends Model
{
    protected $fillable = [
        'entity_type',
        'sevima_id',
        'parent_type',
        'parent_sevima_id',
        'title',
        'subtitle',
        'description',
        'status',
        'period',
        'amount',
        'starts_at',
        'ends_at',
        'is_active',
        'synced_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
