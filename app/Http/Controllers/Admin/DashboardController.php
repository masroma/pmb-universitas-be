<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLead;
use App\Models\CampusSetting;
use App\Models\PmbLocalApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $campusSetting = CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);

        return view('admin.dashboard', [
            'campusSetting' => $campusSetting,
            'stats' => [
                'aiLeads' => AiChatLead::query()->count(),
                'hotLeads' => AiChatLead::query()->whereIn('status', ['hot', 'contact_requested'])->count(),
                'studentAccounts' => User::query()->whereNotNull('api_token')->count(),
                'draftApplications' => PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_DRAFT)->count(),
                'submittedApplications' => PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_SUBMITTED)->count(),
                'verifiedApplications' => PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_VERIFIED)->count(),
                'rejectedApplications' => PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_REJECTED)->count(),
            ],
            'leadFollowUps' => AiChatLead::query()
                ->select('follow_up_status', DB::raw('count(*) as total'))
                ->groupBy('follow_up_status')
                ->pluck('total', 'follow_up_status'),
            'applicationsByCampus' => PmbLocalApplication::query()
                ->select('campus_name', DB::raw('count(*) as total'))
                ->whereNotNull('campus_name')
                ->groupBy('campus_name')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
            'applicationsByProgram' => PmbLocalApplication::query()
                ->select('study_program_name', DB::raw('count(*) as total'))
                ->whereNotNull('study_program_name')
                ->groupBy('study_program_name')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
        ]);
    }
}
