<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbApplicant extends Model
{
    protected $fillable = [
        'sevima_id',
        'registration_period_id',
        'registration_period_name',
        'academic_period_id',
        'wave_id',
        'study_system_id',
        'study_system',
        'registration_path_id',
        'registration_path',
        'registered_at',
        'code',
        'nim',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'nik',
        'address',
        'city',
        'province',
        'country',
        'email',
        'phone',
        'is_active',
        'activated_at',
        'is_final',
        'finalized_at',
        'is_re_registered',
        're_registered_at',
        'is_deleted',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'is_final' => 'boolean',
            'finalized_at' => 'datetime',
            'is_re_registered' => 'boolean',
            're_registered_at' => 'datetime',
            'is_deleted' => 'boolean',
            'raw_payload' => 'array',
        ];
    }
}
