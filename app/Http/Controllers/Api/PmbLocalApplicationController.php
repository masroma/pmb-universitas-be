<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmbLocalApplication;
use App\Models\PmbLocalApplicationDocument;
use App\Models\PmbPeriod;
use App\Models\PmbSevimaRecord;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PmbLocalApplicationController extends Controller
{
    public function options(): JsonResponse
    {
        $activeRegistrationPeriods = $this->activeRegistrationPeriods();
        $activeRegistrationPeriodIds = $activeRegistrationPeriods
            ->pluck('sevima_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->unique()
            ->values();
        $activeAcademicPeriodIds = $activeRegistrationPeriods
            ->map(fn (PmbSevimaRecord $record): ?string => $this->firstFilled($record->raw_payload ?? [], ['periode_akademik', 'id_periode', 'id_periode_akademik']))
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'data' => [
                'academicPeriods' => PmbPeriod::query()
                    ->where('is_active', true)
                    ->whereIn('sevima_id', $activeAcademicPeriodIds)
                    ->orderByDesc('sevima_id')
                    ->get()
                    ->map(fn (PmbPeriod $period): array => [
                        'id' => $period->sevima_id,
                        'name' => $period->name,
                        'academicYear' => $period->academic_year,
                    ])
                    ->values(),
                'registrationPeriods' => $activeRegistrationPeriods
                    ->map(fn (PmbSevimaRecord $record): array => [
                        'id' => $record->sevima_id,
                        'name' => $record->title ?: $record->period ?: $record->sevima_id,
                        'academicPeriodId' => $this->firstFilled($record->raw_payload ?? [], ['periode_akademik', 'id_periode', 'id_periode_akademik']),
                        'status' => $record->status,
                        'startsAt' => $record->starts_at?->toDateString(),
                        'endsAt' => $record->ends_at?->toDateString(),
                    ])
                    ->values(),
                'programOptions' => PmbSevimaRecord::query()
                    ->where('entity_type', 'program-studi-dibuka')
                    ->where('is_active', true)
                    ->whereIn('parent_sevima_id', $activeRegistrationPeriodIds)
                    ->orderBy('parent_sevima_id')
                    ->orderBy('title')
                    ->get()
                    ->map(fn (PmbSevimaRecord $record): array => [
                        'id' => $record->id,
                        'registrationPeriodId' => $record->parent_sevima_id,
                        'studyProgramId' => $this->firstFilled($record->raw_payload ?? [], ['id_program_studi', 'kode_program_studi']),
                        'studyProgramName' => $record->title ?: $this->firstFilled($record->raw_payload ?? [], ['program_studi', 'nama_program_studi', 'nama_prodi']),
                        'registrationPathId' => $this->firstFilled($record->raw_payload ?? [], ['id_jalur_pendaftaran', 'kode_jalur_pendaftaran']),
                        'registrationPathName' => $this->firstFilled($record->raw_payload ?? [], ['jalur_pendaftaran', 'nama_jalur_pendaftaran']),
                        'studySystemId' => $this->firstFilled($record->raw_payload ?? [], ['id_sistem_kuliah', 'kode_sistem_kuliah']),
                        'studySystemName' => $this->firstFilled($record->raw_payload ?? [], ['sistem_kuliah', 'nama_sistem_kuliah']),
                        'fee' => $record->amount,
                    ])
                    ->values(),
            ],
        ]);
    }

    /**
     * @return Collection<int, PmbSevimaRecord>
     */
    private function activeRegistrationPeriods(): Collection
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
            ->orderByDesc('sevima_id')
            ->get();
    }

    public function show(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        return response()->json([
            'data' => $application ? $this->applicationPayload($application) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user) ?? new PmbLocalApplication([
            'user_id' => $user->id,
            'status' => PmbLocalApplication::STATUS_DRAFT,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        if (! in_array($application->status, [PmbLocalApplication::STATUS_DRAFT, PmbLocalApplication::STATUS_REJECTED], true)) {
            return response()->json(['message' => 'Pendaftaran yang sudah submit tidak dapat diedit.'], 422);
        }

        $payload = $request->validate($this->validationRules(false));
        $programOption = PmbSevimaRecord::query()->where('entity_type', 'program-studi-dibuka')->find($payload['program_option_id'] ?? null);
        $registrationPeriod = PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->where('sevima_id', $payload['registration_period_id'] ?? null)
            ->first();
        $academicPeriod = PmbPeriod::query()->where('sevima_id', $payload['academic_period_id'] ?? null)->first();

        $application->fill([
            ...$this->applicationFields($payload, $programOption, $registrationPeriod, $academicPeriod),
            'status' => PmbLocalApplication::STATUS_DRAFT,
            'review_note' => null,
        ])->save();

        return response()->json([
            'data' => $this->applicationPayload($application->fresh('documents')),
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (! $application) {
            return response()->json(['message' => 'Simpan draft pendaftaran terlebih dahulu.'], 422);
        }

        if (! in_array($application->status, [PmbLocalApplication::STATUS_DRAFT, PmbLocalApplication::STATUS_REJECTED], true)) {
            return response()->json(['message' => 'Pendaftaran sudah disubmit.'], 422);
        }

        $missingFields = $this->missingRequiredSubmitFields($application);

        if ($missingFields !== []) {
            return response()->json([
                'message' => 'Lengkapi data wajib sebelum submit pendaftaran.',
                'errors' => [
                    'fields' => $missingFields,
                ],
            ], 422);
        }

        $application->update([
            'status' => PmbLocalApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'review_note' => null,
        ]);

        return response()->json([
            'data' => $this->applicationPayload($application->fresh('documents')),
        ]);
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (! $application) {
            return response()->json(['message' => 'Simpan draft pendaftaran sebelum upload dokumen.'], 422);
        }

        if (! in_array($application->status, [PmbLocalApplication::STATUS_DRAFT, PmbLocalApplication::STATUS_REJECTED], true)) {
            return response()->json(['message' => 'Dokumen tidak dapat diubah setelah pendaftaran disubmit.'], 422);
        }

        $payload = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $existing = $application->documents()->where('type', $payload['type'])->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

        $file = $payload['document'];

        $document = $application->documents()->create([
            'type' => $payload['type'],
            'original_name' => $file->getClientOriginalName(),
            'path' => $file->store('pmb/applications/'.$application->id, 'public'),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        return response()->json([
            'data' => $this->documentPayload($document),
        ], 201);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validationRules(bool $requireAll): array
    {
        $required = $requireAll ? 'required' : 'nullable';

        return [
            'academic_period_id' => [$required, 'string', 'max:255'],
            'registration_period_id' => [$required, 'string', 'max:255'],
            'program_option_id' => [$required, 'integer', 'exists:pmb_sevima_records,id'],
            'name' => [$required, 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => [$required, 'string', 'max:30'],
            'gender' => [$required, 'string', 'max:20'],
            'birth_place' => [$required, 'string', 'max:255'],
            'birth_date' => [$required, 'date'],
            'nik' => [$required, 'string', 'max:30'],
            'address' => [$required, 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'applicant_note' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function applicationFields(array $payload, ?PmbSevimaRecord $programOption, ?PmbSevimaRecord $registrationPeriod, ?PmbPeriod $academicPeriod): array
    {
        $rawProgram = $programOption?->raw_payload ?? [];

        return [
            'academic_period_id' => $payload['academic_period_id'] ?? null,
            'academic_period_name' => $academicPeriod?->name,
            'registration_period_id' => $payload['registration_period_id'] ?? null,
            'registration_period_name' => $registrationPeriod?->title ?: $registrationPeriod?->period,
            'program_option_id' => $programOption?->id,
            'study_program_id' => $this->firstFilled($rawProgram, ['id_program_studi', 'kode_program_studi']),
            'study_program_name' => $programOption?->title ?: $this->firstFilled($rawProgram, ['program_studi', 'nama_program_studi', 'nama_prodi']),
            'registration_path_id' => $this->firstFilled($rawProgram, ['id_jalur_pendaftaran', 'kode_jalur_pendaftaran']),
            'registration_path_name' => $this->firstFilled($rawProgram, ['jalur_pendaftaran', 'nama_jalur_pendaftaran']),
            'study_system_id' => $this->firstFilled($rawProgram, ['id_sistem_kuliah', 'kode_sistem_kuliah']),
            'study_system_name' => $this->firstFilled($rawProgram, ['sistem_kuliah', 'nama_sistem_kuliah']),
            'name' => $payload['name'] ?? null,
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'gender' => $payload['gender'] ?? null,
            'birth_place' => $payload['birth_place'] ?? null,
            'birth_date' => $payload['birth_date'] ?? null,
            'nik' => $payload['nik'] ?? null,
            'address' => $payload['address'] ?? null,
            'city' => $payload['city'] ?? null,
            'province' => $payload['province'] ?? null,
            'country' => $payload['country'] ?? null,
            'applicant_note' => $payload['applicant_note'] ?? null,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function missingRequiredSubmitFields(PmbLocalApplication $application): array
    {
        $requiredFields = [
            'academic_period_id' => 'Periode Akademik',
            'registration_period_id' => 'Periode Pendaftaran',
            'program_option_id' => 'Program Studi',
            'name' => 'Nama Lengkap',
            'phone' => 'No. Handphone',
            'gender' => 'Jenis Kelamin',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'nik' => 'NIK',
            'address' => 'Alamat',
        ];

        return collect($requiredFields)
            ->filter(fn (string $label, string $field): bool => blank($application->{$field}))
            ->values()
            ->all();
    }

    private function applicationForUser(User $user): ?PmbLocalApplication
    {
        return PmbLocalApplication::query()
            ->with('documents')
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function applicationPayload(PmbLocalApplication $application): array
    {
        return [
            'id' => $application->id,
            'status' => $application->status,
            'academicPeriodId' => $application->academic_period_id,
            'academicPeriodName' => $application->academic_period_name,
            'registrationPeriodId' => $application->registration_period_id,
            'registrationPeriodName' => $application->registration_period_name,
            'programOptionId' => $application->program_option_id,
            'studyProgramId' => $application->study_program_id,
            'studyProgramName' => $application->study_program_name,
            'registrationPathId' => $application->registration_path_id,
            'registrationPathName' => $application->registration_path_name,
            'studySystemId' => $application->study_system_id,
            'studySystemName' => $application->study_system_name,
            'name' => $application->name,
            'email' => $application->email,
            'phone' => $application->phone,
            'gender' => $application->gender,
            'birthPlace' => $application->birth_place,
            'birthDate' => $application->birth_date?->toDateString(),
            'nik' => $application->nik,
            'address' => $application->address,
            'city' => $application->city,
            'province' => $application->province,
            'country' => $application->country,
            'applicantNote' => $application->applicant_note,
            'submittedAt' => $application->submitted_at?->toDateTimeString(),
            'reviewedAt' => $application->reviewed_at?->toDateTimeString(),
            'reviewNote' => $application->review_note,
            'documents' => $application->documents->map(fn (PmbLocalApplicationDocument $document): array => $this->documentPayload($document))->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function documentPayload(PmbLocalApplicationDocument $document): array
    {
        return [
            'id' => $document->id,
            'type' => $document->type,
            'originalName' => $document->original_name,
            'url' => $document->url,
            'mimeType' => $document->mime_type,
            'size' => $document->size,
        ];
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

    private function userFromBearerToken(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (! $token || ! str_contains($token, '|')) {
            return null;
        }

        [$userId, $plainToken] = explode('|', $token, 2);
        $user = User::query()->find($userId);

        if (! $user || ! $user->api_token) {
            return null;
        }

        return hash_equals($user->api_token, hash('sha256', $plainToken)) ? $user : null;
    }
}
