<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLead;
use App\Models\CampusSetting;
use App\Models\PmbApplicant;
use App\Models\PmbLocalApplication;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AiDashboardController extends Controller
{
    public function __invoke(): View
    {
        $campusSetting = CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);

        $today = now();
        $currentWindowStart = $today->copy()->subDays(30)->startOfDay();
        $previousWindowStart = $today->copy()->subDays(60)->startOfDay();

        $currentRegistrations = $this->registrationCount($currentWindowStart, $today);
        $previousRegistrations = $this->registrationCount($previousWindowStart, $currentWindowStart);
        $growthRate = $previousRegistrations > 0
            ? (($currentRegistrations - $previousRegistrations) / $previousRegistrations) * 100
            : ($currentRegistrations > 0 ? 100 : 0);

        $dailyAverage = $currentRegistrations / 30;
        $forecastNext30Days = (int) round(max(0, $dailyAverage * 30 * (1 + max(-40, min(40, $growthRate)) / 100)));
        $leadPipeline = AiChatLead::query()->whereIn('status', ['qualified', 'hot', 'contact_requested'])->count();
        $verifiedApplications = PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_VERIFIED)->count();
        $reRegisteredApplicants = PmbApplicant::query()->where('is_deleted', false)->where('is_re_registered', true)->count();
        $submittedApplications = PmbLocalApplication::query()->whereIn('status', [
            PmbLocalApplication::STATUS_SUBMITTED,
            PmbLocalApplication::STATUS_VERIFIED,
            PmbLocalApplication::STATUS_REJECTED,
        ])->count();
        $sevimaApplicants = PmbApplicant::query()->where('is_deleted', false)->count();
        $conversionBase = $submittedApplications + $sevimaApplicants;
        $conversionRate = $conversionBase > 0
            ? round((($verifiedApplications + $reRegisteredApplicants) / $conversionBase) * 100, 1)
            : 0;

        $riskRows = $this->dropoutRiskRows();
        $riskSummary = [
            'high' => $riskRows->where('level', 'Tinggi')->count(),
            'medium' => $riskRows->where('level', 'Sedang')->count(),
            'low' => $riskRows->where('level', 'Rendah')->count(),
        ];

        return view('admin.ai-dashboard.index', [
            'campusSetting' => $campusSetting,
            'summary' => [
                'currentRegistrations' => $currentRegistrations,
                'previousRegistrations' => $previousRegistrations,
                'growthRate' => round($growthRate, 1),
                'forecastNext30Days' => $forecastNext30Days,
                'leadPipeline' => $leadPipeline,
                'conversionRate' => $conversionRate,
                'confidence' => $this->confidenceLabel($currentRegistrations + $previousRegistrations),
            ],
            'programPredictions' => $this->programPredictions(),
            'leadRecommendations' => $this->leadRecommendations(),
            'weeklyTrend' => $this->weeklyTrend(),
            'riskRows' => $riskRows->take(12),
            'riskSummary' => $riskSummary,
        ]);
    }

    private function registrationCount(CarbonInterface $start, CarbonInterface $end): int
    {
        $localApplications = PmbLocalApplication::query()
            ->whereBetween(DB::raw('COALESCE(submitted_at, created_at)'), [$start, $end])
            ->count();

        $sevimaApplicants = PmbApplicant::query()
            ->where('is_deleted', false)
            ->whereBetween(DB::raw('COALESCE(registered_at, created_at)'), [$start, $end])
            ->count();

        return $localApplications + $sevimaApplicants;
    }

    private function confidenceLabel(int $sampleSize): string
    {
        return match (true) {
            $sampleSize >= 120 => 'Tinggi',
            $sampleSize >= 40 => 'Sedang',
            default => 'Awal',
        };
    }

    private function programPredictions(): Collection
    {
        return PmbLocalApplication::query()
            ->select('study_program_name', DB::raw('count(*) as total'))
            ->whereNotNull('study_program_name')
            ->where('created_at', '>=', now()->subDays(60))
            ->groupBy('study_program_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(function (PmbLocalApplication $row): array {
                $forecast = max(1, (int) round($row->total / 2));

                return [
                    'name' => $row->study_program_name,
                    'total' => (int) $row->total,
                    'forecast' => $forecast,
                    'signal' => $row->total >= 10 ? 'Prioritas kampanye' : 'Pantau minat',
                ];
            });
    }

    private function leadRecommendations(): Collection
    {
        return AiChatLead::query()
            ->whereIn('status', ['qualified', 'hot', 'contact_requested'])
            ->orderByDesc('score')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (AiChatLead $lead): array => [
                'name' => $lead->name ?: 'Lead tanpa nama',
                'contact' => $lead->whatsapp ?: $lead->email ?: '-',
                'interest' => $lead->study_program_interest ?: 'Belum memilih prodi',
                'score' => (int) $lead->score,
                'status' => $lead->status,
            ]);
    }

    private function weeklyTrend(): Collection
    {
        $start = now()->subWeeks(7)->startOfWeek();
        $localApplications = PmbLocalApplication::query()
            ->where(DB::raw('COALESCE(submitted_at, created_at)'), '>=', $start)
            ->get(['submitted_at', 'created_at'])
            ->groupBy(fn (PmbLocalApplication $application): string => ($application->submitted_at ?? $application->created_at)->startOfWeek()->format('oW'))
            ->map->count();
        $sevimaApplicants = PmbApplicant::query()
            ->where('is_deleted', false)
            ->where(DB::raw('COALESCE(registered_at, created_at)'), '>=', $start)
            ->get(['registered_at', 'created_at'])
            ->groupBy(fn (PmbApplicant $applicant): string => ($applicant->registered_at ?? $applicant->created_at)->startOfWeek()->format('oW'))
            ->map->count();

        return collect(range(7, 0))
            ->map(function (int $weeksAgo) use ($localApplications, $sevimaApplicants): array {
                $week = now()->subWeeks($weeksAgo)->startOfWeek();
                $weekKey = $week->format('oW');

                return [
                    'label' => $week->format('d M'),
                    'total' => (int) ($localApplications[$weekKey] ?? 0) + (int) ($sevimaApplicants[$weekKey] ?? 0),
                ];
            });
    }

    private function dropoutRiskRows(): Collection
    {
        return PmbApplicant::query()
            ->where('is_deleted', false)
            ->latest('registered_at')
            ->limit(250)
            ->get()
            ->map(function (PmbApplicant $applicant): array {
                $score = 0;
                $signals = [];

                if (! $applicant->is_active) {
                    $score += 30;
                    $signals[] = 'Belum aktif';
                }

                if (! $applicant->is_final) {
                    $score += 20;
                    $signals[] = 'Belum finalisasi';
                }

                if (! $applicant->is_re_registered) {
                    $score += 35;
                    $signals[] = 'Belum daftar ulang';
                }

                if (! $applicant->nim) {
                    $score += 10;
                    $signals[] = 'NIM belum tersedia';
                }

                $registeredAt = $applicant->registered_at ?? $applicant->created_at;
                if ($registeredAt && $registeredAt->lt(now()->subDays(30)) && ! $applicant->is_re_registered) {
                    $score += 15;
                    $signals[] = 'Lewat 30 hari';
                }

                $score = min(100, $score);

                return [
                    'name' => $applicant->name,
                    'code' => $applicant->code ?: $applicant->sevima_id,
                    'phone' => $applicant->phone ?: '-',
                    'path' => $applicant->registration_path ?: '-',
                    'studySystem' => $applicant->study_system ?: '-',
                    'registeredAt' => $registeredAt,
                    'score' => $score,
                    'level' => match (true) {
                        $score >= 70 => 'Tinggi',
                        $score >= 40 => 'Sedang',
                        default => 'Rendah',
                    },
                    'signals' => $signals ?: ['Data lengkap'],
                ];
            })
            ->sortByDesc('score')
            ->values();
    }
}
