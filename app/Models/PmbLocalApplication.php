<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PmbLocalApplication extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'status',
        'academic_period_id',
        'academic_period_name',
        'registration_period_id',
        'registration_period_name',
        'program_option_id',
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
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PmbLocalApplicationDocument::class);
    }
}
