<?php

namespace App\Services;

use App\Models\PmbOpenStudyProgram;
use App\Models\PmbSevimaRecord;
use App\Models\PmbSyncedRegistrationPeriod;
use Illuminate\Support\Carbon;

class PmbRegistrationCascadeSyncService
{
    /**
     * @return array{periods: int, open_programs: int}
     */
    public function syncFromSevimaRecords(): array
    {
        $periodCount = $this->syncRegistrationPeriods();
        $openProgramCount = $this->syncOpenStudyPrograms();

        return [
            'periods' => $periodCount,
            'open_programs' => $openProgramCount,
        ];
    }

    private function syncRegistrationPeriods(): int
    {
        $syncedAt = now();
        $count = 0;

        PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->orderBy('id')
            ->each(function (PmbSevimaRecord $record) use ($syncedAt, &$count): void {
                $payload = $record->raw_payload ?? [];
                $sevimaId = (string) ($record->sevima_id ?: data_get($payload, 'id'));

                if ($sevimaId === '') {
                    return;
                }

                PmbSyncedRegistrationPeriod::query()->updateOrCreate(
                    ['sevima_id' => $sevimaId],
                    [
                        'nama_periode_pendaftaran' => $this->firstFilled($payload, [
                            'nama_periode_pendaftaran',
                            'periode_pendaftaran',
                            'nama_periode',
                            'nama',
                        ]) ?: $record->title,
                        'tanggal_awal_pendaftaran' => $this->dateValue($payload, [
                            'tanggal_awal_pendaftaran',
                            'tanggal_awal',
                        ]) ?: $record->starts_at,
                        'tanggal_akhir_pendaftaran' => $this->dateValue($payload, [
                            'tanggal_akhir_pendaftaran',
                            'tanggal_akhir',
                        ]) ?: $record->ends_at,
                        'status_periode_pendaftaran' => $this->firstFilled($payload, [
                            'status_periode_pendaftaran',
                            'status',
                        ]) ?: $record->status,
                        'id_status_periode_pendaftaran' => $this->firstFilled($payload, [
                            'id_status_periode_pendaftaran',
                        ]),
                        'keterangan' => $this->firstFilled($payload, ['keterangan', 'deskripsi']),
                        'is_active' => $record->is_active,
                        'raw_payload' => $payload,
                        'synced_at' => $syncedAt,
                    ],
                );

                $count++;
            });

        return $count;
    }

    private function syncOpenStudyPrograms(): int
    {
        $syncedAt = now();
        $count = 0;
        $activeSevimaIds = [];

        PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->orderBy('id')
            ->each(function (PmbSevimaRecord $record) use ($syncedAt, &$count, &$activeSevimaIds): void {
                $payload = $record->raw_payload ?? [];
                $sevimaId = (string) ($record->sevima_id ?: data_get($payload, 'id'));

                if ($sevimaId === '') {
                    $sevimaId = 'record-'.$record->id;
                }

                $sistemKuliah = $this->firstFilled($payload, ['sistem_kuliah', 'nama_sistem_kuliah']) ?: $record->subtitle;
                $openProgram = PmbOpenStudyProgram::query()->updateOrCreate(
                    ['sevima_id' => $sevimaId],
                    [
                        'sevima_record_id' => $record->id,
                        'jenjang_program_studi' => $this->firstFilled($payload, [
                            'jenjang_program_studi',
                            'jenjang',
                            'nama_jenjang',
                            'kode_jenjang',
                        ]),
                        'id_program_studi' => $this->intValue($payload, ['id_program_studi', 'kode_program_studi']),
                        'program_studi' => $this->firstFilled($payload, [
                            'program_studi',
                            'nama_program_studi',
                            'nama_prodi',
                        ]) ?: $record->title,
                        'sistem_kuliah' => $sistemKuliah,
                        'lokasi' => $this->extractLokasi($sistemKuliah),
                        'id_periode_pendaftaran' => $this->firstFilled($payload, [
                            'id_periode_pendaftaran',
                            'id_periode_daftar',
                        ]) ?: $record->parent_sevima_id,
                        'nama_periode_pendaftaran' => $this->firstFilled($payload, [
                            'nama_periode_pendaftaran',
                            'periode_pendaftaran',
                            'nama_periode',
                        ]) ?: $record->period,
                        'id_jalur_pendaftaran' => $this->intValue($payload, [
                            'id_jalur_pendaftaran',
                            'kode_jalur_pendaftaran',
                        ]),
                        'jalur_pendaftaran' => $this->firstFilled($payload, [
                            'jalur_pendaftaran',
                            'nama_jalur_pendaftaran',
                        ]),
                        'id_gelombang' => $this->intValue($payload, ['id_gelombang', 'gelombang']),
                        'gelombang' => $this->firstFilled($payload, ['gelombang', 'nama_gelombang']),
                        'registration_fee' => $this->intValue($payload, [
                            'biaya_formulir',
                            'biaya_pendaftaran',
                            'biaya',
                        ]) ?: (int) preg_replace('/\D+/', '', (string) $record->amount),
                        'is_active' => $record->is_active,
                        'raw_payload' => $payload,
                        'synced_at' => $syncedAt,
                    ],
                );

                $activeSevimaIds[] = $openProgram->sevima_id;
                $count++;
            });

        if ($activeSevimaIds !== []) {
            PmbOpenStudyProgram::query()
                ->whereNotIn('sevima_id', $activeSevimaIds)
                ->update(['is_active' => false]);
        }

        return $count;
    }

    private function extractLokasi(?string $sistemKuliah): ?string
    {
        if (! filled($sistemKuliah)) {
            return null;
        }

        if (preg_match('/\(([^)]+)\)\s*$/', $sistemKuliah, $matches)) {
            return trim($matches[1]);
        }

        foreach (['Cipayung', 'Cikarang', 'Kuningan'] as $campus) {
            if (stripos($sistemKuliah, $campus) !== false) {
                return $campus;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (filled($payload[$key] ?? null)) {
                return trim((string) $payload[$key]);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function intValue(array $payload, array $keys): ?int
    {
        $value = $this->firstFilled($payload, $keys);

        return filled($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function dateValue(array $payload, array $keys): ?string
    {
        $value = $this->firstFilled($payload, $keys);

        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
