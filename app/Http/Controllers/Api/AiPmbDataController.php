<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmbOpenStudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AiPmbDataController extends Controller
{
    public function tuitionFees(): JsonResponse
    {
        return response()->json([
            'data' => $this->registrationOptionRows()
                ->map(fn ($row): array => [
                    'id' => $row->tuition_fee_id,
                    'periodId' => $row->period_id,
                    'period' => [
                        'id' => $row->period_id,
                        'name' => $row->period_name,
                        'academicYear' => $row->academic_year,
                        'isActive' => (bool) $row->period_is_active,
                    ],
                    'programLevel' => $row->program_level,
                    'campus' => $row->campus_name,
                    'wave' => $row->wave_name,
                    'studyProgramId' => $row->study_program_id,
                    'studyProgram' => $row->study_program_name,
                    'studyProgramDetail' => [
                        'id' => $row->study_program_id,
                        'level' => $row->program_level,
                        'title' => $row->study_program_name,
                        'accreditation' => $row->accreditation,
                        'isActive' => (bool) $row->program_is_active,
                    ],
                    'registrationPath' => $row->path_name,
                    'classType' => $row->class_name,
                    'registrationFee' => $row->registration_fee,
                    'installmentCount' => $row->installment_count,
                    'installmentAmount' => $row->installment_amount,
                    'semesterFee' => $row->semester_fee,
                    'totalFirstPayment' => $row->total_first_payment,
                    'currency' => $row->currency,
                    'notes' => $row->tuition_notes,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function registrationPaths(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('admission_paths')
                ->join('institutions', 'institutions.id', '=', 'admission_paths.institution_id')
                ->where('institutions.is_active', true)
                ->where('admission_paths.is_active', true)
                ->orderBy('admission_paths.sort_order')
                ->orderBy('admission_paths.name')
                ->select('admission_paths.*', 'institutions.name as institution_name')
                ->get()
                ->map(fn ($path): array => [
                    'id' => $path->id,
                    'institution' => $path->institution_name,
                    'code' => $path->code,
                    'title' => $path->name,
                    'description' => $path->description,
                    'period' => null,
                    'fee' => $this->rupiah((int) $path->registration_fee),
                    'registrationFee' => (int) $path->registration_fee,
                    'startsAt' => null,
                    'endsAt' => null,
                ])
                ->all(),
        ]);
    }

    public function studyPrograms(): JsonResponse
    {
        $programRows = DB::table('study_programs')
            ->leftJoin('faculties', 'faculties.id', '=', 'study_programs.faculty_id')
            ->join('institutions', 'institutions.id', '=', 'study_programs.institution_id')
            ->where('institutions.is_active', true)
            ->where('study_programs.is_active', true)
            ->orderBy('study_programs.sort_order')
            ->orderBy('study_programs.level')
            ->orderBy('study_programs.name')
            ->select('study_programs.*', 'faculties.name as faculty_name', 'institutions.name as institution_name')
            ->get();
        $campusesByProgram = $this->programCampusesByProgramIds($programRows->pluck('id')->map(fn ($id) => (int) $id)->all());

        return response()->json([
            'data' => $programRows
                ->map(fn ($program): array => [
                    'id' => $program->id,
                    'institution' => $program->institution_name,
                    'faculty' => $program->faculty_name,
                    'code' => $program->code,
                    'level' => $program->level,
                    'title' => $program->name,
                    'degree' => $program->degree,
                    'accreditation' => $program->accreditation,
                    'description' => $program->description,
                    'campuses' => $campusesByProgram[(int) $program->id] ?? [],
                ])
                ->all(),
        ]);
    }

    public function scholarships(): JsonResponse
    {
        return response()->json([
            'data' => [
                'paths' => DB::table('admission_paths')
                    ->where('is_active', true)
                    ->where(function ($query): void {
                        $query->where('name', 'like', '%beasiswa%')
                            ->orWhere('description', 'like', '%beasiswa%');
                    })
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn ($path): array => [
                        'id' => $path->id,
                        'title' => $path->name,
                        'description' => $path->description,
                        'fee' => $this->rupiah((int) $path->registration_fee),
                    ])
                    ->all(),
                'scholarships' => DB::table('scholarships')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($scholarship): array => [
                        'id' => $scholarship->id,
                        'code' => $scholarship->code,
                        'title' => $scholarship->name,
                        'description' => $scholarship->description,
                        'requirements' => $this->decodeJson($scholarship->requirements),
                    ])
                    ->all(),
                'information' => $this->contentBlocks('beasiswa')->all(),
            ],
        ]);
    }

    public function pmbContent(): JsonResponse
    {
        return response()->json([
            'data' => $this->contentBlocks()
                ->groupBy('categoryLabel')
                ->map(fn (Collection $sections): array => $sections->values()->all())
                ->all(),
        ]);
    }

    public function classes(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('class_types')
                ->join('institutions', 'institutions.id', '=', 'class_types.institution_id')
                ->where('institutions.is_active', true)
                ->where('class_types.is_active', true)
                ->orderBy('class_types.sort_order')
                ->select('class_types.*', 'institutions.name as institution_name')
                ->get()
                ->map(fn ($classType): array => [
                    'id' => $classType->id,
                    'institution' => $classType->institution_name,
                    'code' => $classType->code,
                    'title' => $classType->name,
                    'scheduleLabel' => $classType->schedule_label,
                    'body' => $classType->description,
                    'items' => array_values(array_filter([
                        $classType->schedule_label,
                        ((bool) $classType->is_online) ? 'Mendukung pembelajaran online/hybrid' : null,
                    ])),
                ])
                ->all(),
        ]);
    }

    public function campusData(): JsonResponse
    {
        $institutions = $this->institutionsWithCampuses();

        return response()->json([
            'data' => [
                'institutions' => $institutions,
                'campusName' => $institutions[0]['campusName'] ?? config('app.name'),
                'website' => $institutions[0]['website'] ?? null,
                'locations' => collect($institutions)->flatMap(fn (array $institution): array => $institution['campuses'])->values()->all(),
            ],
        ]);
    }

    public function registrationFlows(): JsonResponse
    {
        return response()->json([
            'data' => $this->contentBlocks('alur-pendaftaran')
                ->map(fn (array $section): array => [
                    'id' => $section['id'],
                    'title' => $section['title'],
                    'description' => $section['body'],
                    'steps' => collect($section['items'])
                        ->map(fn (string $item, int $index): array => [
                            'id' => $index + 1,
                            'title' => $item,
                            'description' => null,
                        ])
                        ->all(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function registrationPeriods(): JsonResponse
    {
        return response()->json([
            'data' => [
                'academicPeriods' => DB::table('pmb_admission_periods')
                    ->join('institutions', 'institutions.id', '=', 'pmb_admission_periods.institution_id')
                    ->where('institutions.is_active', true)
                    ->where('pmb_admission_periods.is_active', true)
                    ->orderByDesc('pmb_admission_periods.starts_at')
                    ->select('pmb_admission_periods.*', 'institutions.name as institution_name')
                    ->get()
                    ->map(fn ($period): array => [
                        'id' => $period->id,
                        'code' => $period->code,
                        'institution' => $period->institution_name,
                        'name' => $period->name,
                        'academicYear' => $period->academic_year,
                        'startsAt' => $period->starts_at,
                        'endsAt' => $period->ends_at,
                        'brochureUrl' => $period->brochure_url,
                    ])
                    ->all(),
                'registrationPeriods' => DB::table('pmb_waves')
                    ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_waves.admission_period_id')
                    ->where('pmb_waves.is_active', true)
                    ->where('pmb_admission_periods.is_active', true)
                    ->orderBy('pmb_waves.sort_order')
                    ->select('pmb_waves.*', 'pmb_admission_periods.name as period_name', 'pmb_admission_periods.academic_year')
                    ->get()
                    ->map(fn ($wave): array => [
                        'id' => $wave->id,
                        'name' => $wave->name,
                        'academicPeriodId' => $wave->admission_period_id,
                        'academicPeriodName' => $wave->period_name,
                        'academicYear' => $wave->academic_year,
                        'status' => $this->periodStatus($wave->starts_at, $wave->ends_at),
                        'period' => trim(($wave->starts_at ?: '').' - '.($wave->ends_at ?: '')),
                        'startsAt' => $wave->starts_at,
                        'endsAt' => $wave->ends_at,
                    ])
                    ->all(),
            ],
        ]);
    }

    public function admissionRequirements(): JsonResponse
    {
        return $this->contentResponse('syarat');
    }

    public function curriculum(): JsonResponse
    {
        return $this->contentResponse('kurikulum');
    }

    public function pmbContacts(): JsonResponse
    {
        $institutions = $this->institutionsWithCampuses();

        return response()->json([
            'data' => [
                'institutions' => collect($institutions)
                    ->map(fn (array $institution): array => [
                        'id' => $institution['id'],
                        'campusName' => $institution['campusName'],
                        'website' => $institution['website'],
                        'contacts' => collect($institution['campuses'])
                            ->flatMap(fn (array $campus): array => $campus['contacts'])
                            ->values()
                            ->all(),
                    ])->all(),
                'links' => $this->contentBlocks('kontak')->all(),
            ],
        ]);
    }

    public function brochure(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('pmb_admission_periods')
                ->join('institutions', 'institutions.id', '=', 'pmb_admission_periods.institution_id')
                ->where('institutions.is_active', true)
                ->where('pmb_admission_periods.is_active', true)
                ->whereNotNull('pmb_admission_periods.brochure_url')
                ->orderByDesc('pmb_admission_periods.starts_at')
                ->select('pmb_admission_periods.*', 'institutions.name as institution_name')
                ->get()
                ->map(fn ($period): array => [
                    'id' => $period->id,
                    'code' => $period->code,
                    'institution' => $period->institution_name,
                    'name' => $period->name,
                    'academicYear' => $period->academic_year,
                    'startsAt' => $period->starts_at,
                    'endsAt' => $period->ends_at,
                    'url' => $period->brochure_url,
                ])
                ->all(),
        ]);
    }

    public function campusBenefits(): JsonResponse
    {
        return $this->contentResponse('keunggulan');
    }

    public function registrationOptions(): JsonResponse
    {
        $rows = $this->registrationOptionRows();
        $cascadeOptions = $this->cascadeOptionRows();

        return response()->json([
            'data' => [
                'academicPeriods' => $rows
                    ->map(fn ($row): array => [
                        'id' => $row->period_id,
                        'name' => $row->period_name,
                        'academicYear' => $row->academic_year,
                    ])
                    ->unique('id')
                    ->values()
                    ->all(),
                'registrationPeriods' => $rows
                    ->map(fn ($row): array => [
                        'id' => $row->wave_id,
                        'name' => $row->wave_name,
                        'academicPeriodId' => $row->period_id,
                        'startsAt' => $row->wave_starts_at,
                        'endsAt' => $row->wave_ends_at,
                    ])
                    ->unique('id')
                    ->values()
                    ->all(),
                'programOptions' => $rows
                    ->map(fn ($row): array => [
                        'id' => $row->registration_option_id,
                        'registrationPeriodId' => $row->wave_id,
                        'registrationPeriodName' => $row->wave_name,
                        'campusId' => $row->campus_id,
                        'campusName' => $row->campus_name,
                        'studyProgramId' => $row->study_program_id,
                        'studyProgramName' => $row->study_program_name,
                        'programLevel' => $row->program_level,
                        'registrationPathId' => $row->path_id,
                        'registrationPathName' => $row->path_name,
                        'studySystemId' => $row->class_type_id,
                        'studySystemName' => $row->class_name,
                        'fee' => $row->registration_fee,
                        'semesterFee' => $row->semester_fee,
                    ])
                    ->values()
                    ->all(),
                'cascadeOptions' => $cascadeOptions,
                'registrationFlow' => [
                    'steps' => [
                        'Pilih jenjang (S1/S2)',
                        'Pilih program studi',
                        'Pilih lokasi kampus',
                        'Pilih jenis pendaftaran',
                        'Pilih waktu perkuliahan / kelas',
                        'Pilih jalur masuk',
                    ],
                    'source' => 'pmb_open_study_programs',
                ],
            ],
        ]);
    }

    public function openRegistrations(): JsonResponse
    {
        return response()->json([
            'data' => [
                'items' => $this->cascadeOptionRows(),
                'registrationFlow' => [
                    'steps' => [
                        'Pilih jenjang (S1/S2)',
                        'Pilih program studi',
                        'Pilih lokasi kampus',
                        'Pilih jenis pendaftaran',
                        'Pilih waktu perkuliahan / kelas',
                        'Pilih jalur masuk',
                    ],
                ],
                'jenjangOptions' => PmbOpenStudyProgram::query()
                    ->active()
                    ->whereNotNull('jenjang_program_studi')
                    ->distinct()
                    ->orderBy('jenjang_program_studi')
                    ->pluck('jenjang_program_studi')
                    ->values()
                    ->all(),
                'lokasiOptions' => PmbOpenStudyProgram::query()
                    ->active()
                    ->whereNotNull('lokasi')
                    ->distinct()
                    ->orderBy('lokasi')
                    ->pluck('lokasi')
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cascadeOptionRows(): array
    {
        $jenisKeys = [
            'Jalur SMA/SMK',
            'Pindahan',
            'RPL Perolehan SKS',
            'RPL Transfer SKS',
        ];

        return PmbOpenStudyProgram::query()
            ->active()
            ->leftJoin('pmb_synced_registration_periods', 'pmb_synced_registration_periods.sevima_id', '=', 'pmb_open_study_programs.id_periode_pendaftaran')
            ->orderBy('pmb_open_study_programs.jenjang_program_studi')
            ->orderBy('pmb_open_study_programs.program_studi')
            ->orderBy('pmb_open_study_programs.lokasi')
            ->orderBy('pmb_open_study_programs.nama_periode_pendaftaran')
            ->get([
                'pmb_open_study_programs.id',
                'pmb_open_study_programs.jenjang_program_studi',
                'pmb_open_study_programs.id_program_studi',
                'pmb_open_study_programs.program_studi',
                'pmb_open_study_programs.lokasi',
                'pmb_open_study_programs.nama_periode_pendaftaran',
                'pmb_open_study_programs.jalur_pendaftaran',
                'pmb_open_study_programs.id_jalur_pendaftaran',
                'pmb_open_study_programs.gelombang',
                'pmb_open_study_programs.registration_fee',
                'pmb_open_study_programs.is_active',
                'pmb_synced_registration_periods.tanggal_awal_pendaftaran',
                'pmb_synced_registration_periods.tanggal_akhir_pendaftaran',
            ])
            ->map(function ($row) use ($jenisKeys): array {
                $jenjang = (string) ($row->jenjang_program_studi ?? '');
                $jalur = (string) ($row->jalur_pendaftaran ?? '');
                $jenis = $jenjang === 'S2'
                    ? 'Lulusan S1'
                    : (in_array($jalur, $jenisKeys, true) ? $jalur : null);

                return [
                    'id' => $row->id,
                    'programLevel' => $jenjang,
                    'studyProgramId' => $row->id_program_studi,
                    'studyProgramName' => $row->program_studi,
                    'campusName' => $row->lokasi,
                    'jenisPendaftaran' => $jenis,
                    'waktuPerkuliahan' => $row->nama_periode_pendaftaran,
                    'studySystemName' => $row->nama_periode_pendaftaran,
                    'registrationPathId' => $row->id_jalur_pendaftaran,
                    'registrationPathName' => $jalur,
                    'registrationPeriodName' => $row->gelombang,
                    'fee' => (int) $row->registration_fee,
                    'registrationFee' => (int) $row->registration_fee,
                    'registrationStartsAt' => $row->tanggal_awal_pendaftaran,
                    'registrationEndsAt' => $row->tanggal_akhir_pendaftaran,
                    'isActive' => (bool) $row->is_active,
                ];
            })
            ->values()
            ->all();
    }

    private function contentResponse(string $category): JsonResponse
    {
        return response()->json([
            'data' => $this->contentBlocks($category)->values()->all(),
        ]);
    }

    private function contentBlocks(?string $category = null): Collection
    {
        return DB::table('pmb_content_blocks')
            ->join('institutions', 'institutions.id', '=', 'pmb_content_blocks.institution_id')
            ->leftJoin('campuses', 'campuses.id', '=', 'pmb_content_blocks.campus_id')
            ->leftJoin('study_programs', 'study_programs.id', '=', 'pmb_content_blocks.study_program_id')
            ->where('institutions.is_active', true)
            ->where('pmb_content_blocks.is_active', true)
            ->when($category, fn ($query) => $query->where('pmb_content_blocks.category', $category))
            ->orderBy('pmb_content_blocks.category')
            ->orderBy('pmb_content_blocks.sort_order')
            ->select(
                'pmb_content_blocks.*',
                'institutions.name as institution_name',
                'campuses.name as campus_name',
                'study_programs.name as study_program_name',
            )
            ->get()
            ->map(fn ($section): array => [
                'id' => $section->id,
                'institution' => $section->institution_name,
                'campus' => $section->campus_name,
                'studyProgram' => $section->study_program_name,
                'programLevel' => 'Umum',
                'category' => $section->category,
                'categoryLabel' => str($section->category)->replace('-', ' ')->title()->toString(),
                'title' => $section->title,
                'subtitle' => $section->subtitle,
                'body' => $section->body,
                'items' => $this->decodeJson($section->items),
            ]);
    }

    private function institutionsWithCampuses(): array
    {
        $institutions = DB::table('institutions')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->values();
        $institutionIds = $institutions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $campusesByInstitution = $this->campusesByInstitutionIds($institutionIds);

        return $institutions->map(fn ($institution): array => [
            'id' => $institution->id,
            'code' => $institution->code,
            'campusName' => $institution->name,
            'shortName' => $institution->short_name,
            'website' => $institution->website,
            'description' => $institution->description,
            'campuses' => $campusesByInstitution[(int) $institution->id] ?? [],
        ])->all();
    }

    private function campusesByInstitutionIds(array $institutionIds): array
    {
        if ($institutionIds === []) {
            return [];
        }

        $campuses = DB::table('campuses')
            ->whereIn('institution_id', $institutionIds)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->get();
        $campusIds = $campuses->pluck('id')->map(fn ($id) => (int) $id)->all();
        $contactsByCampus = $this->contactsByCampusIds($campusIds);

        return $campuses
            ->map(fn ($campus): array => [
                'institution_id' => (int) $campus->institution_id,
                'id' => $campus->id,
                'code' => $campus->code,
                'name' => $campus->name,
                'city' => $campus->city,
                'province' => $campus->province,
                'address' => $campus->address,
                'mapsUrl' => $campus->maps_url,
                'isMain' => (bool) $campus->is_main,
                'contacts' => $contactsByCampus[(int) $campus->id] ?? [],
            ])
            ->groupBy('institution_id')
            ->map(fn (Collection $items): array => $items->map(function (array $item): array {
                unset($item['institution_id']);

                return $item;
            })->values()->all())
            ->all();
    }

    private function contactsByCampusIds(array $campusIds): array
    {
        if ($campusIds === []) {
            return [];
        }

        return DB::table('campus_contacts')
            ->whereIn('campus_id', $campusIds)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($contact): array => [
                'campus_id' => (int) $contact->campus_id,
                'type' => $contact->type,
                'label' => $contact->label,
                'value' => $contact->value,
                'isPrimary' => (bool) $contact->is_primary,
            ])
            ->groupBy('campus_id')
            ->map(fn (Collection $items): array => $items->map(function (array $item): array {
                unset($item['campus_id']);

                return $item;
            })->values()->all())
            ->all();
    }

    private function programCampusesByProgramIds(array $studyProgramIds): array
    {
        if ($studyProgramIds === []) {
            return [];
        }

        return DB::table('campus_study_programs')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->whereIn('campus_study_programs.study_program_id', $studyProgramIds)
            ->where('campus_study_programs.is_open', true)
            ->where('campuses.is_active', true)
            ->orderBy('campuses.sort_order')
            ->get()
            ->map(fn ($campus): array => [
                'study_program_id' => (int) $campus->study_program_id,
                'id' => $campus->id,
                'code' => $campus->code,
                'name' => $campus->name,
                'city' => $campus->city,
            ])
            ->groupBy('study_program_id')
            ->map(fn (Collection $items): array => $items->map(function (array $item): array {
                unset($item['study_program_id']);

                return $item;
            })->values()->all())
            ->all();
    }

    private function registrationOptionRows(): Collection
    {
        return DB::table('pmb_registration_options')
            ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
            ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
            ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->join('institutions', 'institutions.id', '=', 'study_programs.institution_id')
            ->join('admission_paths', 'admission_paths.id', '=', 'pmb_registration_options.admission_path_id')
            ->leftJoin('class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id')
            ->leftJoin('tuition_fee_schemes', function ($join): void {
                $join->on('tuition_fee_schemes.registration_option_id', '=', 'pmb_registration_options.id')
                    ->where('tuition_fee_schemes.is_active', true);
            })
            ->where('institutions.is_active', true)
            ->where('campuses.is_active', true)
            ->where('study_programs.is_active', true)
            ->where('pmb_admission_periods.is_active', true)
            ->where('pmb_registration_options.is_active', true)
            ->orderBy('pmb_admission_periods.starts_at')
            ->orderBy('pmb_waves.sort_order')
            ->orderBy('campuses.sort_order')
            ->orderBy('study_programs.sort_order')
            ->select([
                'pmb_registration_options.id as registration_option_id',
                'pmb_admission_periods.id as period_id',
                'pmb_admission_periods.name as period_name',
                'pmb_admission_periods.academic_year',
                'pmb_admission_periods.is_active as period_is_active',
                'pmb_waves.id as wave_id',
                'pmb_waves.name as wave_name',
                'pmb_waves.starts_at as wave_starts_at',
                'pmb_waves.ends_at as wave_ends_at',
                'campuses.id as campus_id',
                'campuses.name as campus_name',
                'study_programs.id as study_program_id',
                'study_programs.level as program_level',
                'study_programs.name as study_program_name',
                'study_programs.accreditation',
                'study_programs.is_active as program_is_active',
                'admission_paths.id as path_id',
                'admission_paths.name as path_name',
                'class_types.id as class_type_id',
                'class_types.name as class_name',
                'tuition_fee_schemes.id as tuition_fee_id',
                'tuition_fee_schemes.registration_fee',
                'tuition_fee_schemes.installment_count',
                'tuition_fee_schemes.installment_amount',
                'tuition_fee_schemes.semester_fee',
                'tuition_fee_schemes.total_first_payment',
                'tuition_fee_schemes.currency',
                'tuition_fee_schemes.notes as tuition_notes',
            ])
            ->get();
    }

    private function decodeJson(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
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
}
