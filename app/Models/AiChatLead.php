<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatLead extends Model
{
    public const FOLLOW_UP_NEW = 'new';
    public const FOLLOW_UP_CONTACTED = 'contacted';
    public const FOLLOW_UP_INTERESTED = 'interested';
    public const FOLLOW_UP_REGISTERED = 'registered';
    public const FOLLOW_UP_NOT_INTERESTED = 'not_interested';

    protected $fillable = [
        'ai_chat_conversation_id',
        'name',
        'email',
        'whatsapp',
        'study_program_interest',
        'score',
        'status',
        'follow_up_status',
        'follow_up_note',
        'followed_up_at',
        'followed_up_by',
        'qualification',
        'consented_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'qualification' => 'array',
            'consented_at' => 'datetime',
            'followed_up_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiChatConversation::class, 'ai_chat_conversation_id');
    }

    public function followUpUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_up_by');
    }
}
