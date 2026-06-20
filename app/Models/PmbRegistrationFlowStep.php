<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbRegistrationFlowStep extends Model
{
    protected $fillable = [
        'registration_flow_id',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(PmbRegistrationFlow::class, 'registration_flow_id');
    }
}
