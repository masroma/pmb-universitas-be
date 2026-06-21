<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\PmbInformationSectionController as AdminPmbInformationSectionController;
use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbBenefit;
use App\Models\PmbInformationSection;
use App\Models\PmbPeriod;
use App\Models\PmbRegistrationFlow;
use App\Models\PmbRegistrationPath;
use App\Models\PmbSevimaRecord;
use App\Models\PmbStudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class AiPmbDataController extends Controller
{
    public function registrationPaths(): JsonResponse
    {
        return response()->json([
            'data' => PmbRegistrationPath::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (PmbRegistrationPath $path): array => [
                    'id' => $path->id,
                    'title' => $path->title,
                    'period' => $path->period,
                    'fee' => $path->fee,
                    'startsAt' => $path->starts_at?->toDateString(),
                    'endsAt' => $path->ends_at?->toDateString(),
                ])
                ->all(),
        ]);
    }

    public function studyPrograms(): JsonResponse
    {
        return response()->json([
            'data' => PmbStudyProgram::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('level')
                ->orderBy('title')
                ->get()
                ->map(fn (PmbStudyProgram $program): array => [
                    'id' => $program->id,
                    'level' => $program->level,
                    'title' => $program->title,
                    'accreditation' => $program->accreditation,
                ])
                ->all(),
        ]);
    }

    public function scholarships(): JsonResponse
    {
        return response()->json([
            'data' => [
                'paths' => PmbRegistrationPath::query()
                    ->where('is_active', true)
                    ->where('title', 'like', '%beasiswa%')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (PmbRegistrationPath $path): array => [
                        'id' => $path->id,
                        'title' => $path->title,
                        'period' => $path->period,
                        'fee' => $path->fee,
                        'startsAt' => $path->starts_at?->toDateString(),
                        'endsAt' => $path->ends_at?->toDateString(),
                    ])
                    ->all(),
                'benefits' => PmbBenefit::query()
                    ->where('is_active', true)
                    ->where(function ($query): void {
                        $query
                            ->where('title', 'like', '%beasiswa%')
                            ->orWhere('emphasis', 'like', '%beasiswa%')
                            ->orWhere('description', 'like', '%beasiswa%');
                    })
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->map(fn (PmbBenefit $benefit): array => [
                        'id' => $benefit->id,
                        'title' => $benefit->title,
                        'emphasis' => $benefit->emphasis,
                        'description' => $benefit->description,
                    ])
                    ->all(),
                'information' => $this->informationSections()
                    ->filter(fn (PmbInformationSection $section): bool => $this->containsKeyword($section, 'beasiswa'))
                    ->map(fn (PmbInformationSection $section): array => $this->formatInformationSection($section))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function pmbContent(): JsonResponse
    {
        return response()->json([
            'data' => $this->informationSections()
                ->groupBy(fn (PmbInformationSection $section): string => $section->program_level ?: 'Umum')
                ->map(fn (Collection $sections): array => $sections
                    ->map(fn (PmbInformationSection $section): array => $this->formatInformationSection($section))
                    ->values()
                    ->all())
                ->all(),
        ]);
    }

    public function classes(): JsonResponse
    {
        return response()->json([
            'data' => $this->informationSections('kelas')
                ->map(fn (PmbInformationSection $section): array => $this->formatInformationSection($section))
                ->values()
                ->all(),
        ]);
    }

    public function campusData(): JsonResponse
    {
        $settings = CampusSetting::query()->firstOrCreate(
            ['id' => 1],
            ['campus_name' => config('app.name')],
        );

        return response()->json([
            'data' => [
                'campusName' => $settings->campus_name,
                'address' => $settings->address,
                'website' => $settings->website,
                'phone' => $settings->phone,
                'fax' => $settings->fax,
                'logoUrl' => $settings->logo_url,
                'heroImageUrl' => $settings->hero_image_url,
                'socialMedia' => $settings->social_media,
                'locations' => $this->informationSections('lokasi-kampus')
                    ->map(fn (PmbInformationSection $section): array => $this->formatInformationSection($section))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function registrationFlows(): JsonResponse
    {
        return response()->json([
            'data' => PmbRegistrationFlow::query()
                ->with(['steps' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (PmbRegistrationFlow $flow): array => [
                    'id' => $flow->id,
                    'title' => $flow->title,
                    'description' => $flow->description,
                    'steps' => $flow->steps->map(fn ($step): array => [
                        'id' => $step->id,
                        'title' => $step->title,
                        'description' => $step->description,
                    ])->values()->all(),
                ])
                ->all(),
        ]);
    }

    public function registrationPeriods(): JsonResponse
    {
        return response()->json([
            'data' => [
                'academicPeriods' => PmbPeriod::query()
                    ->where('is_active', true)
                    ->orderByDesc('sevima_id')
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn (PmbPeriod $period): array => [
                        'id' => $period->id,
                        'sevimaId' => $period->sevima_id,
                        'name' => $period->name,
                        'shortName' => $period->short_name,
                        'academicYear' => $period->academic_year,
                        'startsAt' => $period->starts_at?->toDateString(),
                        'endsAt' => $period->ends_at?->toDateString(),
                        'brochureUrl' => $period->brochure_url,
                    ])
                    ->all(),
                'registrationPeriods' => $this->activeRegistrationPeriods()
                    ->map(fn (PmbSevimaRecord $record): array => $this->formatRegistrationPeriod($record))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function admissionRequirements(): JsonResponse
    {
        return $this->informationCategoryResponse('syarat');
    }

    public function curriculum(): JsonResponse
    {
        return $this->informationCategoryResponse('kurikulum');
    }

    public function pmbContacts(): JsonResponse
    {
        $settings = CampusSetting::query()->firstOrCreate(
            ['id' => 1],
            ['campus_name' => config('app.name')],
        );

        return response()->json([
            'data' => [
                'campusName' => $settings->campus_name,
                'website' => $settings->website,
                'phone' => $settings->phone,
                'fax' => $settings->fax,
                'socialMedia' => $settings->social_media,
                'links' => $this->informationSections('kontak')
                    ->map(fn (PmbInformationSection $section): array => $this->formatInformationSection($section))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function brochure(): JsonResponse
    {
        return response()->json([
            'data' => PmbPeriod::query()
                ->whereNotNull('brochure_path')
                ->where('is_active', true)
                ->orderByDesc('sevima_id')
                ->orderByDesc('id')
                ->get()
                ->map(fn (PmbPeriod $period): array => [
                    'id' => $period->id,
                    'sevimaId' => $period->sevima_id,
                    'name' => $period->name,
                    'shortName' => $period->short_name,
                    'academicYear' => $period->academic_year,
                    'startsAt' => $period->starts_at?->toDateString(),
                    'endsAt' => $period->ends_at?->toDateString(),
                    'url' => $period->brochure_url,
                ])
                ->all(),
        ]);
    }

    public function campusBenefits(): JsonResponse
    {
        return response()->json([
            'data' => PmbBenefit::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (PmbBenefit $benefit): array => [
                    'id' => $benefit->id,
                    'icon' => $benefit->icon,
                    'title' => $benefit->title,
                    'emphasis' => $benefit->emphasis,
                    'description' => $benefit->description,
                    'isItalic' => $benefit->is_italic,
                ])
                ->all(),
        ]);
    }

    public function registrationOptions(): JsonResponse
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
                    ->values()
                    ->all(),
                'registrationPeriods' => $activeRegistrationPeriods
                    ->map(fn (PmbSevimaRecord $record): array => $this->formatRegistrationPeriod($record))
                    ->values()
                    ->all(),
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
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /**
     * @return Collection<int, PmbInformationSection>
     */
    private function informationSections(?string $category = null): Collection
    {
        return PmbInformationSection::query()
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('program_level')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function formatInformationSection(PmbInformationSection $section): array
    {
        return [
            'id' => $section->id,
            'programLevel' => $section->program_level ?: 'Umum',
            'category' => $section->category,
            'categoryLabel' => AdminPmbInformationSectionController::CATEGORIES[$section->category] ?? $section->category,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'body' => $section->body,
            'items' => $section->items ?? [],
        ];
    }

    private function informationCategoryResponse(string $category): JsonResponse
    {
        return response()->json([
            'data' => $this->informationSections($category)
                ->map(fn (PmbInformationSection $section): array => $this->formatInformationSection($section))
                ->values()
                ->all(),
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

    private function formatRegistrationPeriod(PmbSevimaRecord $record): array
    {
        return [
            'id' => $record->sevima_id,
            'name' => $record->title ?: $record->period ?: $record->sevima_id,
            'academicPeriodId' => $this->firstFilled($record->raw_payload ?? [], ['periode_akademik', 'id_periode', 'id_periode_akademik']),
            'status' => $record->status,
            'period' => $record->period,
            'startsAt' => $record->starts_at?->toDateString(),
            'endsAt' => $record->ends_at?->toDateString(),
        ];
    }

    private function containsKeyword(PmbInformationSection $section, string $keyword): bool
    {
        $haystack = strtolower(implode(' ', array_filter([
            $section->program_level,
            $section->category,
            $section->title,
            $section->subtitle,
            $section->body,
            ...($section->items ?? []),
        ])));

        return str_contains($haystack, strtolower($keyword));
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
}
