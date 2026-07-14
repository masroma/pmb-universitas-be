<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Pmb\ApplicationSubmittedMail;
use App\Models\PmbLocalApplication;
use App\Models\PmbLocalApplicationDocument;
use App\Models\User;
use App\Services\PmbMailService;
use App\Support\CampusBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PmbLocalApplicationController extends Controller
{
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => [
                'academicPeriods' => DB::table('pmb_admission_periods')
                    ->where('is_active', true)
                    ->orderByDesc('starts_at')
                    ->get()
                    ->map(fn ($period): array => [
                        'id' => $period->id,
                        'name' => $period->name,
                        'academicYear' => $period->academic_year,
                    ])
                    ->values(),
                'registrationPeriods' => DB::table('pmb_waves')
                    ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_waves.admission_period_id')
                    ->where('pmb_waves.is_active', true)
                    ->where('pmb_admission_periods.is_active', true)
                    ->orderBy('pmb_waves.sort_order')
                    ->get([
                        'pmb_waves.id',
                        'pmb_waves.name',
                        'pmb_waves.starts_at',
                        'pmb_waves.ends_at',
                        'pmb_waves.admission_period_id',
                    ])
                    ->map(fn ($wave): array => [
                        'id' => $wave->id,
                        'name' => $wave->name,
                        'academicPeriodId' => $wave->admission_period_id,
                        'status' => $this->periodStatus($wave->starts_at, $wave->ends_at),
                        'startsAt' => $wave->starts_at,
                        'endsAt' => $wave->ends_at,
                    ])
                    ->values(),
                'programOptions' => $this->registrationOptionRows()
                    ->map(fn ($option): array => $this->registrationOptionPayload($option))
                    ->values(),
                'registrationPaths' => DB::table('admission_paths')
                    ->where('is_active', true)
                    ->whereNotNull('sevima_id')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($path): array => [
                        'id' => $path->id,
                        'sevimaId' => $path->sevima_id,
                        'name' => $path->name,
                        'description' => $path->description,
                        'jenisPendaftaran' => $path->jenis_pendaftaran_name,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user);

        if (
            $application
            && ($application->form_payment_status ?? 'pending') === PmbLocalApplication::FORM_PAYMENT_PAID
            && ($application->cbt_status ?? PmbLocalApplication::CBT_STATUS_LOCKED) === PmbLocalApplication::CBT_STATUS_LOCKED
        ) {
            $application->update(['cbt_status' => PmbLocalApplication::CBT_STATUS_AVAILABLE]);
            $application->refresh();
            $application->load('documents');
        }

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

        if ($application->exists && $this->requiresFormPayment($application)) {
            return response()->json(['message' => 'Anda belum membayar formulir pendaftaran.'], 422);
        }

        if ($application->exists && $this->requiresCbtPass($application)) {
            return response()->json(['message' => 'Anda harus lulus tes CBT sebelum mengisi biodata.'], 422);
        }

        $payload = $request->validate($this->validationRules(false));
        $programOption = $this->registrationOption((int) ($payload['program_option_id'] ?? 0));

        if (! $programOption) {
            return response()->json(['message' => 'Pilihan program tidak tersedia.'], 422);
        }

        $admissionPath = $this->admissionPath((int) ($payload['registration_path_id'] ?? 0));

        $application->fill([
            ...$this->applicationFields($payload, $programOption, $admissionPath),
            'status' => PmbLocalApplication::STATUS_DRAFT,
            'review_note' => null,
        ])->save();

        return response()->json([
            'data' => $this->applicationPayload($application->fresh('documents')),
        ]);
    }

    public function storeCascade(Request $request): JsonResponse
    {
        $user = $this->userFromBearerToken($request);

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $application = $this->applicationForUser($user) ?? new PmbLocalApplication([
            'user_id' => $user->id,
            'status' => PmbLocalApplication::STATUS_PAYMENT_PENDING,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        if (! in_array($application->status, [
            PmbLocalApplication::STATUS_DRAFT,
            PmbLocalApplication::STATUS_REJECTED,
            PmbLocalApplication::STATUS_PAYMENT_PENDING,
        ], true)) {
            return response()->json(['message' => 'Pendaftaran yang sudah submit tidak dapat diedit.'], 422);
        }

        $payload = $request->validate($this->cascadeValidationRules());
        $programOption = $this->registrationOption((int) $payload['program_option_id']);

        if (! $programOption) {
            return response()->json(['message' => 'Pilihan program tidak tersedia.'], 422);
        }

        $admissionPath = $this->admissionPath((int) ($payload['registration_path_id'] ?? 0));
        $cascade = $payload['cascade_selection'] ?? [];
        $paymentAmount = (int) ($cascade['registrationFee'] ?? $programOption->registration_fee ?? 0);
        $previousProgramOptionId = (int) ($application->program_option_id ?? 0);
        $keepPaid = $application->form_payment_status === PmbLocalApplication::FORM_PAYMENT_PAID
            && $previousProgramOptionId === (int) $programOption->registration_option_id;

        $application->fill([
            ...$this->applicationFields([
                ...$payload,
                'name' => $payload['name'] ?? $user->name,
                'email' => $payload['email'] ?? $user->email,
                'phone' => $payload['phone'] ?? $user->phone,
            ], $programOption, $admissionPath),
            'status' => $keepPaid ? PmbLocalApplication::STATUS_DRAFT : PmbLocalApplication::STATUS_PAYMENT_PENDING,
            'review_note' => null,
            'form_payment_status' => $keepPaid ? PmbLocalApplication::FORM_PAYMENT_PAID : PmbLocalApplication::FORM_PAYMENT_PENDING,
            'form_payment_bank' => $keepPaid ? $application->form_payment_bank : null,
            'form_payment_amount' => $paymentAmount,
            'form_paid_at' => $keepPaid ? $application->form_paid_at : null,
            'form_paid_by' => $keepPaid ? $application->form_paid_by : null,
            'form_payment_note' => $keepPaid ? $application->form_payment_note : null,
            'cbt_status' => $keepPaid
                ? ($application->cbt_status ?? PmbLocalApplication::CBT_STATUS_AVAILABLE)
                : PmbLocalApplication::CBT_STATUS_LOCKED,
            'cbt_score' => $keepPaid ? $application->cbt_score : null,
            'cbt_attempt_count' => $keepPaid ? $application->cbt_attempt_count : 0,
            'cbt_passed_at' => $keepPaid ? $application->cbt_passed_at : null,
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

        if ($this->requiresFormPayment($application)) {
            return response()->json(['message' => 'Anda belum membayar formulir pendaftaran.'], 422);
        }

        if ($this->requiresCbtPass($application)) {
            return response()->json(['message' => 'Anda harus lulus tes CBT sebelum submit pendaftaran.'], 422);
        }

        if (! in_array($application->status, [PmbLocalApplication::STATUS_DRAFT, PmbLocalApplication::STATUS_REJECTED], true)) {
            return response()->json(['message' => 'Pendaftaran sudah disubmit.'], 422);
        }

        $missingFields = $this->missingRequiredSubmitFields($application);
        $missingDocuments = $this->missingRequiredDocuments($application);

        if ($missingFields !== [] || $missingDocuments !== []) {
            return response()->json([
                'message' => 'Lengkapi data wajib sebelum submit pendaftaran.',
                'errors' => [
                    'fields' => $missingFields,
                    'documents' => $missingDocuments,
                ],
            ], 422);
        }

        $application->update([
            'status' => PmbLocalApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'review_note' => null,
        ]);

        $application = $application->fresh(['documents', 'user']);
        app(PmbMailService::class)->sendToApplication(
            $application,
            new ApplicationSubmittedMail($application, CampusBranding::setting()),
        );

        return response()->json([
            'data' => $this->applicationPayload($application),
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

        if ($this->requiresFormPayment($application)) {
            return response()->json(['message' => 'Anda belum membayar formulir pendaftaran.'], 422);
        }

        if ($this->requiresCbtPass($application)) {
            return response()->json(['message' => 'Anda harus lulus tes CBT sebelum upload dokumen.'], 422);
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
    private function cascadeValidationRules(): array
    {
        return [
            'program_option_id' => ['required', 'integer', 'exists:pmb_registration_options,id'],
            'registration_path_id' => ['nullable', 'integer', 'exists:admission_paths,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'cascade_selection' => ['required', 'array'],
            'cascade_selection.jenjang' => ['required', 'string', 'max:10'],
            'cascade_selection.programStudi' => ['required', 'string', 'max:255'],
            'cascade_selection.lokasi' => ['required', 'string', 'max:100'],
            'cascade_selection.jenisPendaftaran' => ['required', 'string', 'max:100'],
            'cascade_selection.waktuPerkuliahan' => ['required', 'string', 'max:255'],
            'cascade_selection.jalurMasuk' => ['required', 'string', 'max:255'],
            'cascade_selection.jalurMasukId' => ['nullable', 'integer'],
            'cascade_selection.studyProgramId' => ['nullable', 'integer'],
            'cascade_selection.jenisPendaftaranValue' => ['nullable', 'string', 'max:100'],
            'cascade_selection.gelombang' => ['nullable', 'string', 'max:100'],
            'cascade_selection.registrationFee' => ['nullable', 'integer'],
            'cascade_selection.registrationStartsAt' => ['nullable', 'string', 'max:30'],
            'cascade_selection.registrationEndsAt' => ['nullable', 'string', 'max:30'],
            'cascade_selection.openStudyProgramId' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validationRules(bool $requireAll): array
    {
        $required = $requireAll ? 'required' : 'nullable';

        return [
            'academic_period_id' => [$required, 'integer', 'exists:pmb_admission_periods,id'],
            'registration_period_id' => [$required, 'integer', 'exists:pmb_waves,id'],
            'program_option_id' => [$required, 'integer', 'exists:pmb_registration_options,id'],
            'registration_path_id' => [$required, 'integer', 'exists:admission_paths,id'],
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
            'cascade_selection' => ['nullable', 'array'],
            'cascade_selection.jenjang' => ['nullable', 'string', 'max:10'],
            'cascade_selection.programStudi' => ['nullable', 'string', 'max:255'],
            'cascade_selection.lokasi' => ['nullable', 'string', 'max:100'],
            'cascade_selection.jenisPendaftaran' => ['nullable', 'string', 'max:100'],
            'cascade_selection.waktuPerkuliahan' => ['nullable', 'string', 'max:255'],
            'cascade_selection.jalurMasuk' => ['nullable', 'string', 'max:255'],
            'cascade_selection.gelombang' => ['nullable', 'string', 'max:100'],
            'cascade_selection.registrationFee' => ['nullable', 'integer'],
            'cascade_selection.registrationStartsAt' => ['nullable', 'string', 'max:30'],
            'cascade_selection.registrationEndsAt' => ['nullable', 'string', 'max:30'],
            'cascade_selection.studyProgramId' => ['nullable', 'integer'],
            'cascade_selection.jenisPendaftaranValue' => ['nullable', 'string', 'max:100'],
            'cascade_selection.jalurMasukId' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function applicationFields(array $payload, object $programOption, ?object $admissionPath = null): array
    {
        // Jalur pendaftaran dipilih terpisah (data lokal dari SEVIMA); jatuh ke jalur
        // bawaan program option bila belum dipilih.
        $pathId = $admissionPath->id ?? $programOption->path_id;
        $pathName = $admissionPath->name ?? $programOption->path_name;

        $snapshot = $this->registrationOptionPayload($programOption);
        $snapshot['registrationPathId'] = $pathId;
        $snapshot['registrationPathName'] = $pathName;

        $cascade = collect($payload['cascade_selection'] ?? [])
            ->only([
                'jenjang',
                'studyProgramId',
                'programStudi',
                'lokasi',
                'jenisPendaftaran',
                'jenisPendaftaranValue',
                'waktuPerkuliahan',
                'jalurMasuk',
                'jalurMasukId',
                'gelombang',
                'registrationFee',
                'registrationStartsAt',
                'registrationEndsAt',
                'openStudyProgramId',
            ])
            ->filter(fn ($value): bool => filled($value))
            ->all();

        if ($cascade !== []) {
            $snapshot['cascade'] = $cascade;
        }

        $campusName = $cascade['lokasi'] ?? $programOption->campus_name;
        $studyProgramName = $cascade['programStudi'] ?? $programOption->study_program_name;
        $registrationPathName = $cascade['jalurMasuk'] ?? $pathName;
        $studySystemName = $cascade['waktuPerkuliahan'] ?? $programOption->class_name;
        $registrationPeriodName = $cascade['gelombang'] ?? $programOption->wave_name;

        return [
            'academic_period_id' => (string) $programOption->period_id,
            'academic_period_name' => $programOption->period_name,
            'registration_period_id' => $programOption->wave_id ? (string) $programOption->wave_id : null,
            'registration_period_name' => $registrationPeriodName,
            'program_option_id' => $programOption->registration_option_id,
            'pmb_admission_period_id' => $programOption->period_id,
            'pmb_wave_id' => $programOption->wave_id,
            'pmb_registration_option_id' => $programOption->registration_option_id,
            'campus_id' => $programOption->campus_id,
            'campus_name' => $campusName,
            'standalone_study_program_id' => $programOption->study_program_id,
            'admission_path_id' => $pathId,
            'class_type_id' => $programOption->class_type_id,
            'study_program_id' => (string) $programOption->study_program_id,
            'study_program_name' => $studyProgramName,
            'registration_path_id' => (string) $pathId,
            'registration_path_name' => $registrationPathName,
            'study_system_id' => $programOption->class_type_id ? (string) $programOption->class_type_id : null,
            'study_system_name' => $studySystemName,
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
            'registration_snapshot' => $snapshot,
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
            'registration_path_id' => 'Jalur Pendaftaran',
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

    /**
     * @return array<int, string>
     */
    private function missingRequiredDocuments(PmbLocalApplication $application): array
    {
        $requiredDocuments = [
            'ktp' => 'KTP',
            'ijazah' => 'Ijazah/Surat Lulus',
            'foto' => 'Pas Foto',
        ];
        $uploadedTypes = $application->documents->pluck('type')->all();

        return collect($requiredDocuments)
            ->reject(fn (string $label, string $type): bool => in_array($type, $uploadedTypes, true))
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
            'formPaymentStatus' => $application->form_payment_status ?? PmbLocalApplication::FORM_PAYMENT_PENDING,
            'formPaymentBank' => $application->form_payment_bank,
            'formPaymentAmount' => (int) ($application->form_payment_amount ?? 0),
            'virtualAccountNumber' => $this->virtualAccountNumber($application),
            'virtualAccounts' => $this->virtualAccounts($application),
            'formPaidAt' => $application->form_paid_at?->toDateTimeString(),
            'formPaymentNote' => $application->form_payment_note,
            'cbtStatus' => $application->cbt_status ?? PmbLocalApplication::CBT_STATUS_LOCKED,
            'cbtScore' => $application->cbt_score,
            'cbtAttemptCount' => (int) ($application->cbt_attempt_count ?? 0),
            'cbtPassedAt' => $application->cbt_passed_at?->toDateTimeString(),
            'academicPeriodId' => $application->academic_period_id,
            'academicPeriodName' => $application->academic_period_name,
            'registrationPeriodId' => $application->registration_period_id,
            'registrationPeriodName' => $application->registration_period_name,
            'programOptionId' => $application->program_option_id,
            'campusId' => $application->campus_id,
            'campusName' => $application->campus_name,
            'studyProgramId' => $application->study_program_id,
            'studyProgramName' => $application->study_program_name,
            'registrationPathId' => $application->registration_path_id,
            'registrationPathName' => $application->registration_path_name,
            'studySystemId' => $application->study_system_id,
            'studySystemName' => $application->study_system_name,
            'registrationSnapshot' => $application->registration_snapshot,
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

    private function registrationOption(int $id): ?object
    {
        return $this->registrationOptionRows()
            ->first(fn ($option): bool => (int) $option->registration_option_id === $id);
    }

    private function admissionPath(int $id): ?object
    {
        if ($id === 0) {
            return null;
        }

        return DB::table('admission_paths')
            ->where('id', $id)
            ->where('is_active', true)
            ->first(['id', 'name', 'sevima_id', 'jenis_pendaftaran_name']);
    }

    private function registrationOptionPayload(object $option): array
    {
        return [
            'id' => $option->registration_option_id,
            'academicPeriodId' => $option->period_id,
            'academicPeriodName' => $option->period_name,
            'registrationPeriodId' => $option->wave_id,
            'registrationPeriodName' => $option->wave_name,
            'campusId' => $option->campus_id,
            'campusName' => $option->campus_name,
            'studyProgramId' => $option->study_program_id,
            'studyProgramName' => $option->study_program_name,
            'programLevel' => $option->program_level,
            'registrationPathId' => $option->path_id,
            'registrationPathName' => $option->path_name,
            'studySystemId' => $option->class_type_id,
            'studySystemName' => $option->class_name,
            'fee' => $this->rupiah((int) $option->registration_fee),
            'registrationFee' => (int) $option->registration_fee,
            'semesterFee' => (int) $option->semester_fee,
            'installmentAmount' => (int) $option->installment_amount,
            'installmentCount' => (int) $option->installment_count,
        ];
    }

    private function registrationOptionRows()
    {
        return DB::table('pmb_registration_options')
            ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
            ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
            ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->join('admission_paths', 'admission_paths.id', '=', 'pmb_registration_options.admission_path_id')
            ->leftJoin('class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id')
            ->leftJoin('tuition_fee_schemes', function ($join): void {
                $join->on('tuition_fee_schemes.registration_option_id', '=', 'pmb_registration_options.id')
                    ->where('tuition_fee_schemes.is_active', true);
            })
            ->where('pmb_registration_options.is_active', true)
            ->where('pmb_admission_periods.is_active', true)
            ->where('campus_study_programs.is_open', true)
            ->where('campuses.is_active', true)
            ->where('study_programs.is_active', true)
            ->where('admission_paths.is_active', true)
            ->orderBy('pmb_admission_periods.starts_at')
            ->orderBy('pmb_waves.sort_order')
            ->orderBy('campuses.sort_order')
            ->orderBy('study_programs.sort_order')
            ->select([
                'pmb_registration_options.id as registration_option_id',
                'pmb_admission_periods.id as period_id',
                'pmb_admission_periods.name as period_name',
                'pmb_admission_periods.academic_year',
                'pmb_waves.id as wave_id',
                'pmb_waves.name as wave_name',
                'campuses.id as campus_id',
                'campuses.name as campus_name',
                'study_programs.id as study_program_id',
                'study_programs.level as program_level',
                'study_programs.name as study_program_name',
                'admission_paths.id as path_id',
                'admission_paths.name as path_name',
                'class_types.id as class_type_id',
                'class_types.name as class_name',
                DB::raw('COALESCE(tuition_fee_schemes.registration_fee, admission_paths.registration_fee, 0) as registration_fee'),
                DB::raw('COALESCE(tuition_fee_schemes.installment_count, 0) as installment_count'),
                DB::raw('COALESCE(tuition_fee_schemes.installment_amount, 0) as installment_amount'),
                DB::raw('COALESCE(tuition_fee_schemes.semester_fee, 0) as semester_fee'),
            ])
            ->get();
    }

    private function periodStatus(?string $startsAt, ?string $endsAt): string
    {
        $today = now()->toDateString();

        if ($startsAt && $today < $startsAt) {
            return 'upcoming';
        }

        if ($endsAt && $today > $endsAt) {
            return 'closed';
        }

        return 'open';
    }

    private function rupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function requiresFormPayment(PmbLocalApplication $application): bool
    {
        if (! $application->program_option_id) {
            return false;
        }

        return ($application->form_payment_status ?? PmbLocalApplication::FORM_PAYMENT_PENDING) !== PmbLocalApplication::FORM_PAYMENT_PAID;
    }

    private function requiresCbtPass(PmbLocalApplication $application): bool
    {
        if (! $application->program_option_id) {
            return false;
        }

        if ($this->requiresFormPayment($application)) {
            return true;
        }

        return ! $application->hasPassedCbt();
    }

    private function virtualAccountNumber(PmbLocalApplication $application): string
    {
        $institutionCode = '3901';
        $year = now()->format('y');
        $sequence = str_pad((string) $application->id, 8, '0', STR_PAD_LEFT);

        return $institutionCode.$year.$sequence;
    }

    /**
     * @return array<string, string>
     */
    private function virtualAccounts(PmbLocalApplication $application): array
    {
        $base = $this->virtualAccountNumber($application);

        return [
            'bca' => '014'.$base,
            'mandiri' => '008'.$base,
            'cimb' => '022'.$base,
        ];
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
