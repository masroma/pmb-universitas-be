<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PmbLocalApplication extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    public const FORM_PAYMENT_PENDING = 'pending';
    public const FORM_PAYMENT_PAID = 'paid';

    public const CBT_STATUS_LOCKED = 'locked';
    public const CBT_STATUS_AVAILABLE = 'available';
    public const CBT_STATUS_IN_PROGRESS = 'in_progress';
    public const CBT_STATUS_PASSED = 'passed';
    public const CBT_STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'status',
        'form_payment_status',
        'form_payment_bank',
        'form_payment_amount',
        'form_paid_at',
        'form_paid_by',
        'form_payment_note',
        'cbt_status',
        'cbt_score',
        'cbt_attempt_count',
        'cbt_passed_at',
        'academic_period_id',
        'academic_period_name',
        'registration_period_id',
        'registration_period_name',
        'program_option_id',
        'pmb_admission_period_id',
        'pmb_wave_id',
        'pmb_registration_option_id',
        'campus_id',
        'campus_name',
        'standalone_study_program_id',
        'admission_path_id',
        'class_type_id',
        'study_program_id',
        'study_program_name',
        'registration_path_id',
        'registration_path_name',
        'study_system_id',
        'study_system_name',
        'name',
        'email',
        'phone',
        'gender',
        'birth_place',
        'birth_date',
        'nik',
        'address',
        'city',
        'province',
        'country',
        'applicant_note',
        'registration_snapshot',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registration_snapshot' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'form_paid_at' => 'datetime',
            'cbt_passed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(PmbCbtAttempt::class);
    }

    public function hasPassedCbt(): bool
    {
        return ($this->cbt_status ?? self::CBT_STATUS_LOCKED) === self::CBT_STATUS_PASSED;
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function formPaymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'form_paid_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PmbLocalApplicationDocument::class);
    }
}
