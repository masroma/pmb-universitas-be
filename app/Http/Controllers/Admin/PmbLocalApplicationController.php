<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbLocalApplication;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        $before = $application->only(['status', 'review_note', 'reviewed_at', 'reviewed_by']);
        $application->update([
            'status' => $payload['status'],
            'review_note' => $payload['review_note'] ?? null,
            'reviewed_at' => in_array($payload['status'], [PmbLocalApplication::STATUS_VERIFIED, PmbLocalApplication::STATUS_REJECTED], true) ? now() : null,
            'reviewed_by' => in_array($payload['status'], [PmbLocalApplication::STATUS_VERIFIED, PmbLocalApplication::STATUS_REJECTED], true) ? $request->user()->id : null,
        ]);
        AuditLogger::record('application_status_updated', 'pmb_local_applications', $application->id, $before, $application->fresh()->only(['status', 'review_note', 'reviewed_at', 'reviewed_by']), $request);

        return redirect()
            ->route('admin.local-applications.show', $application)
            ->with('status', 'Status pendaftaran berhasil diperbarui.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'pendaftar-pmb-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama', 'Email', 'WhatsApp', 'Status', 'Kampus', 'Prodi', 'Jalur', 'Kelas', 'Periode', 'Submitted At']);

            PmbLocalApplication::query()
                ->orderByDesc('submitted_at')
                ->chunk(200, function ($applications) use ($handle): void {
                    foreach ($applications as $application) {
                        fputcsv($handle, [
                            $application->name,
                            $application->email,
                            $application->phone,
                            $application->status,
                            $application->campus_name,
                            $application->study_program_name,
                            $application->registration_path_name,
                            $application->study_system_name,
                            $application->registration_period_name,
                            $application->submitted_at,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
