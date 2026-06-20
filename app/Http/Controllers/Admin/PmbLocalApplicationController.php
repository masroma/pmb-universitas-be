<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbLocalApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PmbLocalApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedStatus = $request->string('status')->toString();

        $applications = PmbLocalApplication::query()
            ->with(['user', 'documents'])
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('registration_period_name', 'like', "%{$search}%")
                    ->orWhere('study_program_name', 'like', "%{$search}%");
            }))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pmb-local-applications.index', [
            'applications' => $applications,
            'campusSetting' => $this->campusSetting(),
            'search' => $search,
            'selectedStatus' => $selectedStatus,
            'statusLabels' => $this->statusLabels(),
            'totalApplications' => PmbLocalApplication::query()->count(),
            'totalSubmitted' => PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_SUBMITTED)->count(),
            'totalVerified' => PmbLocalApplication::query()->where('status', PmbLocalApplication::STATUS_VERIFIED)->count(),
        ]);
    }

    public function show(PmbLocalApplication $application): View
    {
        $application->load(['user', 'documents', 'reviewer']);

        return view('admin.pmb-local-applications.show', [
            'application' => $application,
            'campusSetting' => $this->campusSetting(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function updateStatus(Request $request, PmbLocalApplication $application): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:submitted,verified,rejected'],
            'review_note' => ['nullable', 'string'],
        ]);

        $application->update([
            'status' => $payload['status'],
            'review_note' => $payload['review_note'] ?? null,
            'reviewed_at' => in_array($payload['status'], [PmbLocalApplication::STATUS_VERIFIED, PmbLocalApplication::STATUS_REJECTED], true) ? now() : null,
            'reviewed_by' => in_array($payload['status'], [PmbLocalApplication::STATUS_VERIFIED, PmbLocalApplication::STATUS_REJECTED], true) ? $request->user()->id : null,
        ]);

        return redirect()
            ->route('admin.local-applications.show', $application)
            ->with('status', 'Status pendaftaran berhasil diperbarui.');
    }

    /**
     * @return array<string, string>
     */
    private function statusLabels(): array
    {
        return [
            PmbLocalApplication::STATUS_DRAFT => 'Draft',
            PmbLocalApplication::STATUS_SUBMITTED => 'Menunggu Review',
            PmbLocalApplication::STATUS_VERIFIED => 'Terverifikasi',
            PmbLocalApplication::STATUS_REJECTED => 'Ditolak/Revisi',
        ];
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }
}
