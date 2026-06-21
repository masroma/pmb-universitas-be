<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TuitionFee extends Model
{
    protected $fillable = [
        'pmb_period_id',
        'pmb_study_program_id',
        'program_level',
        'campus',
        'wave',
        'study_program',
        'registration_fee',
        'installment_count',
        'installment_amount',
        'semester_fee',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'pmb_period_id' => 'integer',
            'pmb_study_program_id' => 'integer',
            'registration_fee' => 'integer',
            'installment_count' => 'integer',
            'installment_amount' => 'integer',
            'semester_fee' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PmbPeriod::class, 'pmb_period_id');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(PmbStudyProgram::class, 'pmb_study_program_id');
    }
}
