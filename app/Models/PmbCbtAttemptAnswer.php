<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PmbCbtAttemptAnswer extends Model
{
    protected $fillable = [
        'pmb_cbt_attempt_id',
        'pmb_cbt_question_id',
        'question_order',
        'selected_option',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(PmbCbtAttempt::class, 'pmb_cbt_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PmbCbtQuestion::class, 'pmb_cbt_question_id');
    }
}
