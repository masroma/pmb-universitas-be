<?php

namespace App\Services;

use App\Models\PmbApplicant;
use App\Models\PmbRegistrationPath;
use App\Models\PmbPeriod;
use App\Models\PmbSevimaRecord;
use App\Models\PmbStudyProgram;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Stringable;
use Throwable;

class SevimaPmbSyncService
{
    /**
     * @return array<string, int>
     */
    public function sync(bool $withApplicantDetails = true): array
    {
        if (! $this->hasCredentials()) {
            throw new RuntimeException('Credential SEVIMA belum diisi di environment.');
        }

        $counts = [];
        $academicPeriodId = (string) config('sevima.periode_akademik');

        $referencePeriods = $this->syncEndpoint(
            'periode',
            '/siakadcloud/v1/periode',
            ['o-id' => 'desc'],
        );
        $counts['periode'] = $referencePeriods->count();

        $periods = $this->syncEndpoint(
            'periode-pendaftaran',
            '/siakadcloud/v1/periode-pendaftaran',
            ['f-periode_akademik' => $academicPeriodId, 'o-id' => 'desc'],
            parentType: 'periode',
            parentSevimaId: $academicPeriodId,
        );
        $counts['periode-pendaftaran'] = $periods->count();

        foreach ($periods as $period) {
            $periodId = $period->sevima_id;

            if (! $periodId) {
                continue;
            }

            $openPrograms = $this->syncEndpoint(
                'program-studi-dibuka',
                "/siakadcloud/v1/periode-pendaftaran/{$periodId}/program-studi-dibuka",
                parentType: 'periode-pendaftaran',
                parentSevimaId: $periodId,
            );
            $counts['program-studi-dibuka'] = ($counts['program-studi-dibuka'] ?? 0) + $openPrograms->count();

        }

        $applicants = $this->syncEndpoint(
            'pendaftar',
            '/siakadcloud/v1/pendaftar',
            ['f-id_periode' => $academicPeriodId, 'o-id' => 'desc'],
            parentType: 'periode',
            parentSevimaId: $academicPeriodId,
        );
        $counts['pendaftar'] = $applicants->count();

        foreach ([
            'jalur-pendaftaran' => '/siakadcloud/v1/jalur-pendaftaran',
            'gelombang' => '/siakadcloud/v1/gelombang',
            'sistem-kuliah' => '/siakadcloud/v1/sistem-kuliah',
            'program-studi-pendaftar' => '/siakadcloud/v1/program-studi-pendaftar',
        ] as $entityType => $endpoint) {
            $records = $this->syncEndpoint($entityType, $endpoint);
            $counts[$entityType] = ($counts[$entityType] ?? 0) + $records->count();
        }

        if ($withApplicantDetails) {
            $this->syncApplicantDetails($counts);
        }

        $this->refreshLandingTables();

        return $counts;
    }

    public function refreshLocalTables(): void
    {
        $this->refreshLandingTables();
    }

    /**
     * @return Collection<int, PmbSevimaRecord>
     */
    private function syncEndpoint(
        string $entityType,
        string $endpoint,
        array $query = [],
        ?string $parentType = null,
        ?string $parentSevimaId = null,
    ): Collection {
        return $this->sevimaItems($endpoint, $query)
            ->map(fn (array $item, int $index): PmbSevimaRecord => $this->storeRecord(
                $entityType,
                $item,
                $index,
                $parentType,
                $parentSevimaId,
            ));
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function syncProgramApplicants(array &$counts): void
    {
        PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-pendaftar')
            ->whereNotNull('sevima_id')
            ->each(function (PmbSevimaRecord $program) use (&$counts): void {
                $records = $this->syncEndpoint(
                    'pendaftar-program-studi',
                    "/siakadcloud/v1/program-studi-pendaftar/{$program->sevima_id}/pendaftar",
                    parentType: 'program-studi-pendaftar',
                    parentSevimaId: $program->sevima_id,
                );

                $counts['pendaftar-program-studi'] = ($counts['pendaftar-program-studi'] ?? 0) + $records->count();
            });
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function syncSelectionApplicants(array &$counts): void
    {
        PmbSevimaRecord::query()
            ->where('entity_type', 'seleksi-pmb')
            ->whereNotNull('sevima_id')
            ->each(function (PmbSevimaRecord $selection) use (&$counts): void {
                $records = $this->syncEndpoint(
                    'pendaftar-seleksi',
                    "/siakadcloud/v1/seleksi-pmb/{$selection->sevima_id}/pendaftar",
                    parentType: 'seleksi-pmb',
                    parentSevimaId: $selection->sevima_id,
                );

                $counts['pendaftar-seleksi'] = ($counts['pendaftar-seleksi'] ?? 0) + $records->count();
            });
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function syncApplicantDetails(array &$counts): void
    {
        PmbSevimaRecord::query()
            ->where('entity_type', 'pendaftar')
            ->whereNotNull('sevima_id')
            ->each(function (PmbSevimaRecord $applicant) use (&$counts): void {
                foreach ([
                    'invoice' => "/siakadcloud/v1/pendaftar/{$applicant->sevima_id}/invoice",
                    'program-studi-pendaftar-pilihan' => "/siakadcloud/v1/pendaftar/{$applicant->sevima_id}/program-studi-pendaftar",
                    'nilai-seleksi' => "/siakadcloud/v1/pendaftar/{$applicant->sevima_id}/nilai-seleksi",
                ] as $entityType => $endpoint) {
                    $records = $this->syncEndpoint(
                        $entityType,
                        $endpoint,
                        parentType: 'pendaftar',
                        parentSevimaId: $applicant->sevima_id,
                    );

                    $counts[$entityType] = ($counts[$entityType] ?? 0) + $records->count();
                }
            });
    }

    private function refreshLandingTables(): void
    {
        $this->refreshPeriods();
        $this->refreshApplicants();

        $activeRegistrationPeriodIds = $this->activeRegistrationPeriodIds();

        $this->refreshStudyPrograms($activeRegistrationPeriodIds);
        $this->refreshRegistrationPaths($activeRegistrationPeriodIds);
    }

    /**
     * @param  Collection<int, string>  $activeRegistrationPeriodIds
     */
    private function refreshRegistrationPaths(Collection $activeRegistrationPeriodIds): void
    {
        $paths = $activeRegistrationPeriodIds->isEmpty()
            ? collect()
            : PmbSevimaRecord::query()
                ->where('entity_type', 'periode-pendaftaran')
                ->whereIn('sevima_id', $activeRegistrationPeriodIds)
                ->orderBy('id')
                ->get();

        if ($activeRegistrationPeriodIds->isEmpty()) {
            PmbRegistrationPath::query()->delete();
        } else {
            PmbRegistrationPath::query()
                ->whereNull('sevima_id')
                ->orWhereNotIn('sevima_id', $activeRegistrationPeriodIds)
                ->delete();
        }

        $paths->each(function (PmbSevimaRecord $record, int $index): void {
            $payload = $record->raw_payload ?? [];

            PmbRegistrationPath::query()->updateOrCreate(
                ['sevima_id' => $record->sevima_id ?: $record->entity_type.'-'.$record->id],
                [
                    'title' => $this->firstFilled($payload, ['jalur_pendaftaran', 'nama_jalur_pendaftaran', 'nama_jalur']) ?: $record->title ?: 'Jalur Pendaftaran',
                    'period' => $this->dateRangeText($record->starts_at, $record->ends_at) ?: $record->period,
                    'fee' => $record->amount ?: $this->paidLabel($payload),
                    'starts_at' => $record->starts_at,
                    'ends_at' => $record->ends_at,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'raw_payload' => $payload,
                ],
            );
        });
    }

    private function refreshPeriods(): void
    {
        PmbSevimaRecord::query()
            ->where('entity_type', 'periode')
            ->orderByDesc('sevima_id')
            ->get()
            ->each(function (PmbSevimaRecord $record): void {
                $payload = $record->raw_payload ?? [];

                PmbPeriod::query()->updateOrCreate(
                    ['sevima_id' => (string) $record->sevima_id],
                    [
                        'name' => $this->firstFilled($payload, ['nama_periode', 'nama', 'periode']) ?: $record->title ?: (string) $record->sevima_id,
                        'short_name' => $this->firstFilled($payload, ['nama_singkat']),
                        'academic_year' => $this->firstFilled($payload, ['tahun_ajar', 'tahun_akademik']),
                        'starts_at' => $this->dateValue($payload, ['tanggal_awal']),
                        'ends_at' => $this->dateValue($payload, ['tanggal_akhir']),
                        'midterm_starts_at' => $this->dateValue($payload, ['tanggal_awal_uts']),
                        'midterm_ends_at' => $this->dateValue($payload, ['tanggal_akhir_uts']),
                        'final_starts_at' => $this->dateValue($payload, ['tanggal_awal_uas']),
                        'final_ends_at' => $this->dateValue($payload, ['tanggal_akhir_uas']),
                        'is_active' => $this->isActive($payload),
                        'raw_payload' => $payload,
                    ],
                );
            });
    }

    /**
     * @param  Collection<int, string>  $activeRegistrationPeriodIds
     */
    private function refreshStudyPrograms(Collection $activeRegistrationPeriodIds): void
    {
        $programs = $activeRegistrationPeriodIds->isEmpty()
            ? collect()
            : PmbSevimaRecord::query()
                ->where('entity_type', 'program-studi-dibuka')
                ->where('parent_type', 'periode-pendaftaran')
                ->whereIn('parent_sevima_id', $activeRegistrationPeriodIds)
                ->where('is_active', true)
                ->orderBy('id')
                ->get()
                ->filter(fn (PmbSevimaRecord $record): bool => filled(data_get($record->raw_payload ?? [], 'id_program_studi')))
                ->unique(fn (PmbSevimaRecord $record): string => (string) data_get($record->raw_payload ?? [], 'id_program_studi'))
                ->values();

        if ($programs->isEmpty() && $activeRegistrationPeriodIds->isNotEmpty()) {
            $programs = PmbSevimaRecord::query()
                ->where('entity_type', 'program-studi-pendaftar')
                ->where('is_active', true)
                ->orderBy('id')
                ->get()
                ->filter(fn (PmbSevimaRecord $record): bool => $this->belongsToActiveRegistrationPeriod($record, $activeRegistrationPeriodIds))
                ->filter(fn (PmbSevimaRecord $record): bool => filled($this->firstFilled($record->raw_payload ?? [], ['id_program_studi', 'kode_program_studi'])))
                ->unique(fn (PmbSevimaRecord $record): string => (string) $this->firstFilled($record->raw_payload ?? [], ['id_program_studi', 'kode_program_studi']))
                ->values();
        }

        $activeIds = $programs
            ->map(fn (PmbSevimaRecord $record): string => (string) $this->masterStudyProgramId($record))
            ->filter()
            ->values();

        if ($activeIds->isEmpty()) {
            PmbStudyProgram::query()->delete();
        } else {
            PmbStudyProgram::query()
                ->whereNull('sevima_id')
                ->orWhereNotIn('sevima_id', $activeIds)
                ->delete();
        }

        $programs->each(function (PmbSevimaRecord $record, int $index): void {
            $payload = $record->raw_payload ?? [];
            $studyProgramId = $this->masterStudyProgramId($record);

            if (! $studyProgramId) {
                return;
            }

            PmbStudyProgram::query()->updateOrCreate(
                ['sevima_id' => $studyProgramId],
                [
                    'level' => $this->firstFilled($payload, ['jenjang_program_studi', 'jenjang', 'nama_jenjang', 'kode_jenjang', 'strata']),
                    'title' => $this->firstFilled($payload, ['program_studi', 'nama_program_studi', 'nama_prodi']) ?: $record->title ?: 'Program Studi',
                    'accreditation' => $this->firstFilled($payload, ['akreditasi', 'peringkat_akreditasi', 'status_akreditasi']) ?: '---',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'raw_payload' => $payload,
                ],
            );
        });
    }

    private function masterStudyProgramId(PmbSevimaRecord $record): ?string
    {
        return $this->firstFilled($record->raw_payload ?? [], ['id_program_studi', 'kode_program_studi']);
    }

    /**
     * @return Collection<int, string>
     */
    private function activeRegistrationPeriodIds(): Collection
    {
        $today = now()->toDateString();

        return PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->where('is_active', true)
            ->where(function ($query) use ($today): void {
                $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today);
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            })
            ->pluck('sevima_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->unique()
            ->values();
    }

    /**
     * @param  Collection<int, string>  $activeRegistrationPeriodIds
     */
    private function belongsToActiveRegistrationPeriod(PmbSevimaRecord $record, Collection $activeRegistrationPeriodIds): bool
    {
        $periodId = $this->firstFilled($record->raw_payload ?? [], [
            'id_periode_pendaftaran',
            'id_periode_daftar',
            'id_periode_pmb',
        ]);

        return filled($periodId) && $activeRegistrationPeriodIds->contains((string) $periodId);
    }

    private function refreshApplicants(): void
    {
        PmbSevimaRecord::query()
            ->where('entity_type', 'pendaftar')
            ->orderByDesc('sevima_id')
            ->get()
            ->each(function (PmbSevimaRecord $record): void {
                $payload = $record->raw_payload ?? [];

                PmbApplicant::query()->updateOrCreate(
                    ['sevima_id' => (string) $record->sevima_id],
                    [
                        'registration_period_id' => $this->firstFilled($payload, ['id_periode_daftar']),
                        'registration_period_name' => $this->firstFilled($payload, ['periode_daftar']),
                        'academic_period_id' => $this->firstFilled($payload, ['id_periode']),
                        'wave_id' => $this->firstFilled($payload, ['id_gelombang']),
                        'study_system_id' => $this->firstFilled($payload, ['id_sistem_kuliah']),
                        'study_system' => $this->firstFilled($payload, ['sistem_kuliah']),
                        'registration_path_id' => $this->firstFilled($payload, ['id_jalur_pendaftaran']),
                        'registration_path' => $this->firstFilled($payload, ['jalur_pendaftaran']),
                        'registered_at' => $this->dateValue($payload, ['tanggal_daftar']),
                        'code' => $this->firstFilled($payload, ['kode']),
                        'nim' => $this->firstFilled($payload, ['nim']),
                        'name' => $this->firstFilled($payload, ['nama']) ?: $record->title ?: 'Pendaftar',
                        'gender' => $this->firstFilled($payload, ['jenis_kelamin']),
                        'birth_place' => $this->firstFilled($payload, ['tempat_lahir']),
                        'birth_date' => $this->dateValue($payload, ['tanggal_lahir']),
                        'nik' => $this->firstFilled($payload, ['nik']),
                        'address' => $this->firstFilled($payload, ['alamat']),
                        'city' => $this->firstFilled($payload, ['kota']),
                        'province' => $this->firstFilled($payload, ['nama_provinsi', 'provinsi']),
                        'country' => $this->firstFilled($payload, ['negara']),
                        'email' => $this->firstFilled($payload, ['email']),
                        'phone' => $this->firstFilled($payload, ['nomor_hp', 'telepon']),
                        'is_active' => $this->flagValue($payload, 'is_aktif'),
                        'activated_at' => $this->dateValue($payload, ['tanggal_aktif']),
                        'is_final' => $this->flagValue($payload, 'is_final'),
                        'finalized_at' => $this->dateValue($payload, ['tanggal_final']),
                        'is_re_registered' => $this->flagValue($payload, 'is_daftar_ulang'),
                        're_registered_at' => $this->dateValue($payload, ['tanggal_daftar_ulang']),
                        'is_deleted' => $this->flagValue($payload, 'is_deleted'),
                        'raw_payload' => $payload,
                    ],
                );
            });
    }

    private function storeRecord(
        string $entityType,
        array $item,
        int $index,
        ?string $parentType = null,
        ?string $parentSevimaId = null,
    ): PmbSevimaRecord {
        $payload = $this->normalizePayload($item);
        $sevimaId = $this->externalId($payload) ?: $this->fallbackId($payload, $entityType, $parentSevimaId, $index);

        return PmbSevimaRecord::query()->updateOrCreate(
            [
                'entity_type' => $entityType,
                'sevima_id' => $sevimaId,
                'parent_type' => $parentType,
                'parent_sevima_id' => $parentSevimaId,
            ],
            [
                'title' => $this->title($entityType, $payload),
                'subtitle' => $this->subtitle($payload),
                'description' => $this->firstFilled($payload, ['deskripsi', 'description', 'keterangan', 'catatan']),
                'status' => $this->firstFilled($payload, ['status', 'status_pendaftaran', 'status_periode_pendaftaran', 'status_lulus', 'kelulusan']),
                'period' => $this->periodText($payload),
                'amount' => $this->feeText($this->firstFilled($payload, ['biaya_formulir', 'biaya_pendaftaran', 'biaya', 'nominal', 'tarif', 'total'])),
                'starts_at' => $this->dateValue($payload, ['tanggal_awal_pendaftaran', 'tanggal_awal', 'tanggal_mulai', 'tgl_mulai', 'mulai', 'start_date', 'starts_at']),
                'ends_at' => $this->dateValue($payload, ['tanggal_akhir_pendaftaran', 'tanggal_akhir', 'tanggal_selesai', 'tgl_selesai', 'selesai', 'end_date', 'ends_at']),
                'is_active' => $this->isActive($payload),
                'synced_at' => now(),
                'raw_payload' => $payload,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(array $item): array
    {
        $attributes = data_get($item, 'attributes');
        $payload = is_array($attributes) ? $attributes : $item;

        foreach (['id', 'type', 'url', 'relationships'] as $key) {
            if (array_key_exists($key, $item)) {
                $payload[$key] = $item[$key];
            }
        }

        return $payload;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function sevimaItems(string $endpoint, array $query = []): Collection
    {
        $items = collect();
        $page = 1;
        $query = array_filter($query, fn ($value): bool => filled($value));

        do {
            $response = $this->sevimaGet($endpoint, [
                ...$query,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException($this->responseErrorMessage($endpoint, $response->status(), $response->body()));
            }

            $body = $response->json();
            $pageItems = $this->extractItems($body);
            $items = $items->merge($pageItems);

            $lastPage = (int) data_get($body, 'meta.last_page', data_get($body, 'last_page', $page));
            $currentPage = (int) data_get($body, 'meta.current_page', data_get($body, 'current_page', $page));
            $page++;
        } while ($pageItems !== [] && $currentPage < $lastPage);

        return $items->values();
    }

    private function sevimaGet(string $endpoint, array $query): \Illuminate\Http\Client\Response
    {
        $attempts = max(1, (int) config('sevima.max_attempts', 3));
        $retrySeconds = max(1, (int) config('sevima.rate_limit_retry_seconds', 15));

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $response = Http::baseUrl(config('sevima.base_url'))
                ->acceptJson()
                ->withHeaders($this->headers())
                ->get($endpoint, $query);

            if ($response->status() !== 429 || $attempt === $attempts) {
                $this->throttle();

                return $response;
            }

            sleep($retrySeconds);
        }

        throw new RuntimeException("SEVIMA {$endpoint} gagal dipanggil.");
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-App-Key' => (string) config('sevima.app_key'),
            'X-Secret-Key' => (string) config('sevima.secret_key'),
        ];

        if (filled(config('sevima.bearer_token'))) {
            $headers['Authorization'] = 'Bearer '.config('sevima.bearer_token');
        }

        return $headers;
    }

    private function throttle(): void
    {
        $delay = max(0, (int) config('sevima.request_delay_seconds', 2));

        if ($delay > 0) {
            sleep($delay);
        }
    }

    private function responseErrorMessage(string $endpoint, int $status, string|Stringable|null $body): string
    {
        $message = "SEVIMA {$endpoint} gagal: HTTP {$status}";
        $body = trim((string) $body);

        if ($body === '') {
            return $message;
        }

        return $message.' - '.Str::limit($body, 500);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractItems(mixed $body): array
    {
        if (! is_array($body)) {
            return [];
        }

        foreach (['data.data', 'data.items', 'data', 'items', 'result.data', 'result'] as $path) {
            $value = data_get($body, $path);

            if (is_array($value) && array_is_list($value)) {
                return array_values(array_filter($value, 'is_array'));
            }
        }

        if (array_is_list($body)) {
            return array_values(array_filter($body, 'is_array'));
        }

        return ArrLike::isAssoc($body) ? [$body] : [];
    }

    private function hasCredentials(): bool
    {
        return filled(config('sevima.app_key')) && filled(config('sevima.secret_key'));
    }

    private function externalId(array $item): ?string
    {
        return $this->firstFilled($item, [
            'id',
            'id_pendaftar',
            'id_program_studi_pendaftar',
            'id_periode',
            'id_periode_akademik',
            'id_periode_pendaftaran',
            'id_program_studi',
            'id_jalur_pendaftaran',
            'id_gelombang',
            'id_sistem_kuliah',
            'id_seleksi_pmb',
            'kode',
            'uuid',
        ]);
    }

    private function fallbackId(array $item, string $entityType, ?string $parentSevimaId, int $index): string
    {
        $fingerprint = md5(json_encode($item, JSON_THROW_ON_ERROR));

        return Str::limit($entityType.'-'.$parentSevimaId.'-'.$index.'-'.$fingerprint, 255, '');
    }

    private function title(string $entityType, array $item): ?string
    {
        $keys = match ($entityType) {
            'pendaftar', 'pendaftar-program-studi', 'pendaftar-seleksi' => ['nama', 'nama_pendaftar', 'nama_lengkap', 'nim', 'no_pendaftaran'],
            'periode' => ['nama_periode', 'periode', 'tahun_akademik', 'nama', 'kode'],
            'periode-pendaftaran' => ['nama_periode_pendaftaran', 'periode_pendaftaran', 'nama_periode', 'periode', 'nama'],
            'jalur-pendaftaran' => ['nama_jalur_pendaftaran', 'jalur_pendaftaran', 'nama_jalur', 'jalur', 'nama'],
            'gelombang' => ['nama_gelombang', 'gelombang', 'nama'],
            'sistem-kuliah' => ['nama_sistem_kuliah', 'sistem_kuliah', 'nama'],
            'seleksi-pmb' => ['nama_seleksi', 'seleksi', 'nama'],
            default => ['nama_program_studi', 'nama_prodi', 'program_studi', 'nama', 'title'],
        };

        return $this->firstFilled($item, $keys);
    }

    private function subtitle(array $item): ?string
    {
        return $this->firstFilled($item, [
            'jenjang',
            'nama_jenjang',
            'kode_jenjang',
            'tahun_akademik',
            'periode_akademik',
            'email',
            'no_pendaftaran',
            'nim',
            'kode',
        ]);
    }

    private function firstFilled(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($item, $key);

            if (filled($value) && ! is_array($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function dateValue(array $item, array $keys): ?Carbon
    {
        $value = $this->firstFilled($item, $keys);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function periodText(array $item): ?string
    {
        $startsAt = $this->dateValue($item, ['tanggal_awal_pendaftaran', 'tanggal_awal', 'tanggal_mulai', 'tgl_mulai', 'mulai', 'start_date']);
        $endsAt = $this->dateValue($item, ['tanggal_akhir_pendaftaran', 'tanggal_akhir', 'tanggal_selesai', 'tgl_selesai', 'selesai', 'end_date']);

        if ($startsAt && $endsAt) {
            return $startsAt->translatedFormat('j M Y').' - '.$endsAt->translatedFormat('j M Y');
        }

        $explicit = $this->firstFilled($item, ['periode', 'periode_pendaftaran', 'periode_akademik', 'nama_periode']);

        if ($explicit) {
            return $explicit;
        }

        return null;
    }

    private function feeText(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (! is_numeric($value)) {
            return $value;
        }

        return 'Rp. '.number_format((float) $value, 0, ',', '.');
    }

    private function isActive(array $item): bool
    {
        if (in_array(strtolower((string) $this->firstFilled($item, ['is_deleted', 'deleted'])), ['1', 'true', 'y', 'ya'], true)) {
            return false;
        }

        $status = strtolower((string) $this->firstFilled($item, ['id_status_periode_pendaftaran', 'status_periode_pendaftaran', 'status', 'status_aktif', 'aktif', 'is_active', 'is_aktif']));

        if (in_array($status, ['0', 'false', 'n', 'tidak aktif', 'nonaktif', 'inactive', 'i', 'tutup'], true)) {
            return false;
        }

        return true;
    }

    private function dateRangeText(mixed $startsAt, mixed $endsAt): ?string
    {
        if (! $startsAt || ! $endsAt) {
            return null;
        }

        return Carbon::parse($startsAt)->translatedFormat('j M Y').' - '.Carbon::parse($endsAt)->translatedFormat('j M Y');
    }

    private function paidLabel(array $item): ?string
    {
        $isPaid = $this->firstFilled($item, ['is_berbayar']);

        return match ((string) $isPaid) {
            '1' => 'Berbayar',
            '0' => 'Gratis',
            default => null,
        };
    }

    private function flagValue(array $item, string $key): bool
    {
        return in_array(strtolower((string) data_get($item, $key)), ['1', 'true', 'y', 'ya'], true);
    }
}

class ArrLike
{
    public static function isAssoc(array $value): bool
    {
        return $value !== [] && array_keys($value) !== range(0, count($value) - 1);
    }
}
