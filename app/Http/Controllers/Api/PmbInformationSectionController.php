<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\PmbInformationSectionController as AdminPmbInformationSectionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PmbInformationSectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = DB::table('pmb_content_blocks')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (): string => 'Umum')
            ->map(fn ($programSections): array => $programSections
                ->map(fn ($section): array => [
                    'id' => $section->id,
                    'programLevel' => 'Umum',
                    'category' => $section->category,
                    'categoryLabel' => AdminPmbInformationSectionController::CATEGORIES[$section->category] ?? $section->category,
                    'title' => $section->title,
                    'subtitle' => $section->subtitle,
                    'body' => $section->body,
                    'items' => $this->decodeJson($section->items),
                ])
                ->values()
                ->all())
            ->all();

        return response()->json([
            'data' => $sections,
        ]);
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
