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
                'tuitionFees' => $this->tuitionFees(),
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
            ->orderBy('jenis_pendaftaran_name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($path): array => [
                'title' => $path->name,
                'period' => $this->activePeriodLabel(),
                'fee' => 'Rp '.number_format((int) $path->registration_fee, 0, ',', '.'),
                'description' => $path->description,
                'jenisPendaftaran' => $path->jenis_pendaftaran_name ?: 'Lainnya',
                'jenisPendaftaranId' => $path->jenis_pendaftaran_id,
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

    private function tuitionFees(): array
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
            ->where('campuses.is_active', true)
            ->where('study_programs.is_active', true)
            ->where('admission_paths.is_active', true)
            ->orderBy('study_programs.level')
            ->orderBy('study_programs.sort_order')
            ->orderBy('campuses.sort_order')
            ->orderBy('admission_paths.sort_order')
            ->select([
                'pmb_registration_options.id',
                'study_programs.level as program_level',
                'study_programs.name as study_program',
                'campuses.name as campus',
                'pmb_waves.name as wave',
                'admission_paths.name as registration_path',
                'class_types.name as class_type',
                DB::raw('COALESCE(tuition_fee_schemes.registration_fee, admission_paths.registration_fee, 0) as registration_fee'),
                'tuition_fee_schemes.installment_count',
                'tuition_fee_schemes.installment_amount',
                'tuition_fee_schemes.semester_fee',
                'tuition_fee_schemes.total_first_payment',
            ])
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'programLevel' => $row->program_level ?: '-',
                'studyProgram' => $row->study_program,
                'campus' => $row->campus,
                'wave' => $row->wave,
                'registrationPath' => $row->registration_path,
                'classType' => $row->class_type,
                'registrationFee' => (int) ($row->registration_fee ?? 0),
                'installmentCount' => $row->installment_count !== null ? (int) $row->installment_count : null,
                'installmentAmount' => $row->installment_amount !== null ? (int) $row->installment_amount : null,
                'semesterFee' => $row->semester_fee !== null ? (int) $row->semester_fee : null,
                'totalFirstPayment' => $row->total_first_payment !== null ? (int) $row->total_first_payment : null,
            ])
            ->values()
            ->all();
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
