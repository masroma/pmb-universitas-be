<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbOpenStudyProgram extends Model
{
    protected $fillable = [
        'sevima_id',
        'sevima_record_id',
        'jenjang_program_studi',
        'id_program_studi',
        'program_studi',
        'sistem_kuliah',
        'lokasi',
        'id_periode_pendaftaran',
        'nama_periode_pendaftaran',
        'id_jalur_pendaftaran',
        'jalur_pendaftaran',
        'id_gelombang',
        'gelombang',
        'registration_fee',
        'is_active',
        'raw_payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'raw_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function sevimaRecord(): BelongsTo
    {
        return $this->belongsTo(PmbSevimaRecord::class, 'sevima_record_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForJenjang(Builder $query, string $jenjang): Builder
    {
        return $query->where('jenjang_program_studi', $jenjang);
    }

    public function scopeForStudyProgram(Builder $query, int $studyProgramId): Builder
    {
        return $query->where('id_program_studi', $studyProgramId);
    }

    public function scopeForLokasi(Builder $query, string $lokasi): Builder
    {
        return $query->where(function (Builder $builder) use ($lokasi): void {
            $builder->where('lokasi', $lokasi)
                ->orWhere('sistem_kuliah', 'like', '%'.$lokasi.'%');
        });
    }

    public function scopeWithActivePeriod(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where(function (Builder $builder) use ($today): void {
            $builder->whereNull('id_periode_pendaftaran')
                ->orWhereExists(function ($subquery) use ($today): void {
                    $subquery->selectRaw('1')
                        ->from('pmb_synced_registration_periods')
                        ->whereColumn('pmb_synced_registration_periods.sevima_id', 'pmb_open_study_programs.id_periode_pendaftaran')
                        ->where(function ($periodQuery) use ($today): void {
                            $periodQuery->whereNull('tanggal_akhir_pendaftaran')
                                ->orWhereDate('tanggal_akhir_pendaftaran', '>=', $today);
                        });
                });
        });
    }
}
