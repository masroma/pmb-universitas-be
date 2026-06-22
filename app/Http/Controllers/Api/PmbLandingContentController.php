<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PmbLandingContentController extends Controller
{
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
        return DB::table('pmb_content_blocks')
            ->where('category', 'keunggulan')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->flatMap(function ($block): array {
                $items = $this->decodeJson($block->items);

                if ($items !== []) {
                    return collect($items)
                        ->map(fn (string $item, int $index): array => [
                            'icon' => strtoupper(substr($item, 0, 1)),
                            'title' => $item,
                            'emphasis' => null,
                            'description' => $block->body,
                            'isItalic' => false,
                            'sortOrder' => $block->sort_order + $index,
                        ])
                        ->all();
                }

                return [[
                    'icon' => strtoupper(substr($block->title, 0, 1)),
                    'title' => $block->title,
                    'emphasis' => null,
                    'description' => $block->body,
                    'isItalic' => false,
                    'sortOrder' => $block->sort_order,
                ]];
            })
            ->sortBy('sortOrder')
            ->values()
            ->all();
    }

    private function programs(): array
    {
        return DB::table('study_programs')
            ->join('campus_study_programs', 'campus_study_programs.study_program_id', '=', 'study_programs.id')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->where('study_programs.is_active', true)
            ->where('campus_study_programs.is_open', true)
            ->where('campuses.is_active', true)
            ->orderBy('study_programs.sort_order')
            ->orderBy('campuses.sort_order')
            ->select([
                'study_programs.id',
                'study_programs.level',
                'study_programs.name',
                'study_programs.accreditation',
                'campuses.name as campus_name',
            ])
            ->get()
            ->groupBy('id')
            ->map(fn ($rows): array => [
                'level' => $rows->first()->level ?: '-',
                'title' => $rows->first()->name,
                'accreditation' => $rows->first()->accreditation ?: '---',
                'campus' => $rows->pluck('campus_name')->filter()->unique()->join(', '),
            ])
            ->values()
            ->all();
    }

    private function paths(): array
    {
        return DB::table('admission_paths')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($path): array => [
                'title' => $path->name,
                'period' => $this->activePeriodLabel(),
                'fee' => 'Rp '.number_format((int) $path->registration_fee, 0, ',', '.'),
                'description' => $path->description,
            ])
            ->all();
    }

    private function registrationFlows(): array
    {
        return DB::table('pmb_content_blocks')
            ->where('category', 'alur-pendaftaran')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($block): array => [
                'title' => $block->title,
                'description' => $block->body,
                'accentClass' => 'bg-blue-700',
                'steps' => collect($this->decodeJson($block->items))
                    ->map(fn (string $item): array => [
                        'title' => $item,
                        'description' => null,
                    ])
                    ->all(),
            ])
            ->all();
    }

    private function brochureUrl(): ?string
    {
        return DB::table('pmb_admission_periods')
            ->where('is_active', true)
            ->whereNotNull('brochure_url')
            ->orderByDesc('starts_at')
            ->value('brochure_url');
    }

    private function activePeriodLabel(): string
    {
        $period = DB::table('pmb_admission_periods')
            ->where('is_active', true)
            ->orderByDesc('starts_at')
            ->first(['starts_at', 'ends_at']);

        if (! $period) {
            return '-';
        }

        return trim(($period->starts_at ?: '').' - '.($period->ends_at ?: ''), ' -');
    }

    private function decodeJson(?string $value): array
    {
        if (! $value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
