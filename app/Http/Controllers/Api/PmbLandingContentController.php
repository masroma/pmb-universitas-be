<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PmbBenefit;
use App\Models\PmbPeriod;
use App\Models\PmbRegistrationFlow;
use App\Models\PmbRegistrationPath;
use App\Models\PmbSevimaRecord;
use App\Models\PmbStudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class PmbLandingContentController extends Controller
{
    /**
     * @var Collection<int, PmbSevimaRecord>|null
     */
    private ?Collection $activeRegistrationPeriods = null;

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'benefits' => $this->benefits(),
                'programs' => $this->programs(),
                'paths' => $this->paths(),
                'registrationFlows' => $this->registrationFlows(),
                'brochureUrl' => $this->brochureUrl(),
            ],
        ]);
    }

    private function benefits(): array
    {
        return PmbBenefit::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PmbBenefit $benefit): array => [
                'icon' => $benefit->icon,
                'title' => $benefit->title,
                'emphasis' => $benefit->emphasis,
                'description' => $benefit->description,
                'isItalic' => $benefit->is_italic,
            ])
            ->all();
    }

    private function programs(): array
    {
        $activeStudyProgramIds = $this->activeStudyProgramIds();

        if ($activeStudyProgramIds->isEmpty()) {
            return [];
        }

        return PmbStudyProgram::query()
            ->where('is_active', true)
            ->whereIn('sevima_id', $activeStudyProgramIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PmbStudyProgram $program): array => [
                'level' => $program->level ?: '-',
                'title' => $program->title,
                'accreditation' => $program->accreditation ?: '---',
            ])
            ->all();
    }

    private function paths(): array
    {
        $activeRegistrationPeriodIds = $this->activeRegistrationPeriodIds();

        if ($activeRegistrationPeriodIds->isEmpty()) {
            return [];
        }

        return PmbRegistrationPath::query()
            ->where('is_active', true)
            ->whereIn('sevima_id', $activeRegistrationPeriodIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PmbRegistrationPath $path): array => [
                'title' => $path->title,
                'period' => $path->period ?: '-',
                'fee' => $path->fee ?: '-',
            ])
            ->all();
    }

    /**
     * @return Collection<int, string>
     */
    private function activeStudyProgramIds(): Collection
    {
        $activeRegistrationPeriodIds = $this->activeRegistrationPeriodIds();

        if ($activeRegistrationPeriodIds->isEmpty()) {
            return collect();
        }

        $programIds = PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->where('parent_type', 'periode-pendaftaran')
            ->whereIn('parent_sevima_id', $activeRegistrationPeriodIds)
            ->where('is_active', true)
            ->get(['raw_payload'])
            ->map(fn (PmbSevimaRecord $record): ?string => $this->firstFilled($record->raw_payload ?? [], ['id_program_studi', 'kode_program_studi']))
            ->filter()
            ->unique()
            ->values();

        if ($programIds->isNotEmpty()) {
            return $programIds;
        }

        return PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-pendaftar')
            ->where('is_active', true)
            ->get(['raw_payload'])
            ->filter(fn (PmbSevimaRecord $record): bool => $this->belongsToActiveRegistrationPeriod($record, $activeRegistrationPeriodIds))
            ->map(fn (PmbSevimaRecord $record): ?string => $this->firstFilled($record->raw_payload ?? [], ['id_program_studi', 'kode_program_studi']))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function activeRegistrationPeriodIds(): Collection
    {
        return $this->activeRegistrationPeriods()
            ->pluck('sevima_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, PmbSevimaRecord>
     */
    private function activeRegistrationPeriods(): Collection
    {
        if ($this->activeRegistrationPeriods !== null) {
            return $this->activeRegistrationPeriods;
        }

        $today = now()->toDateString();

        $this->activeRegistrationPeriods = PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->where('is_active', true)
            ->where(function ($query) use ($today): void {
                $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today);
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            })
            ->orderByDesc('sevima_id')
            ->get(['sevima_id', 'raw_payload']);

        return $this->activeRegistrationPeriods;
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

    private function registrationFlows(): array
    {
        return PmbRegistrationFlow::query()
            ->with(['steps' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PmbRegistrationFlow $flow): array => [
                'title' => $flow->title,
                'description' => $flow->description,
                'accentClass' => $flow->accent_class,
                'steps' => $flow->steps->map(fn ($step): array => [
                    'title' => $step->title,
                    'description' => $step->description,
                ])->all(),
            ])
            ->all();
    }

    private function brochureUrl(): ?string
    {
        $today = now()->toDateString();

        return PmbPeriod::query()
            ->whereNotNull('brochure_path')
            ->where('is_active', true)
            ->where(function ($query) use ($today): void {
                $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today);
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today);
            })
            ->orderByDesc('sevima_id')
            ->first()
            ?->brochure_url;
    }
}
