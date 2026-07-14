<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PmbCbtAttempt extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'pmb_local_application_id',
        'attempt_number',
        'status',
        'score',
        'total_questions',
        'correct_count',
        'passed',
        'started_at',
        'expires_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PmbLocalApplication::class, 'pmb_local_application_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PmbCbtAttemptAnswer::class, 'pmb_cbt_attempt_id')->orderBy('question_order');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
