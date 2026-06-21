<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChatConversation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'visitor_name',
        'visitor_email',
        'visitor_whatsapp',
        'lead_status',
        'lead_score',
        'lead_interest',
        'lead_qualified_at',
        'contact_consent_at',
    ];

    protected function casts(): array
    {
        return [
            'lead_score' => 'integer',
            'lead_qualified_at' => 'datetime',
            'contact_consent_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class);
    }

    public function lead(): HasOne
    {
        return $this->hasOne(AiChatLead::class);
    }
}
