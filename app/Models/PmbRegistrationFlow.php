<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PmbRegistrationFlow extends Model
{
    protected $fillable = [
        'title',
        'description',
        'accent_class',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(PmbRegistrationFlowStep::class, 'registration_flow_id');
    }
}
