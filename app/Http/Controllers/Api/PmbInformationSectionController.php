<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\PmbInformationSectionController as AdminPmbInformationSectionController;
use App\Http\Controllers\Controller;
use App\Models\PmbInformationSection;
use Illuminate\Http\JsonResponse;

class PmbInformationSectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = PmbInformationSection::query()
            ->where('is_active', true)
            ->orderBy('program_level')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (PmbInformationSection $section): string => $section->program_level ?: 'Umum')
            ->map(fn ($programSections): array => $programSections
                ->map(fn (PmbInformationSection $section): array => [
                    'id' => $section->id,
                    'programLevel' => $section->program_level ?: 'Umum',
                    'category' => $section->category,
                    'categoryLabel' => AdminPmbInformationSectionController::CATEGORIES[$section->category] ?? $section->category,
                    'title' => $section->title,
                    'subtitle' => $section->subtitle,
                    'body' => $section->body,
                    'items' => $section->items ?? [],
                ])
                ->values()
                ->all())
            ->all();

        return response()->json([
            'data' => $sections,
        ]);
    }
}
