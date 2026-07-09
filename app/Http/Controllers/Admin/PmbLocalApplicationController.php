<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Pmb\ApplicationRejectedMail;
use App\Mail\Pmb\ApplicationVerifiedMail;
use App\Models\CampusSetting;
use App\Models\PmbLocalApplication;
use App\Services\PmbMailService;
use App\Support\AuditLogger;
use App\Support\CampusBranding;
use App\Support\PmbCascadeSnapshot;
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
                    ->orWhere('study_program_name', 'like', "%{$search}%")
                    ->orWhere('campus_name', 'like', "%{$search}%")
                    ->orWhere('registration_path_name', 'like', "%{$search}%")
                    ->orWhere('study_system_name', 'like', "%{$search}%")
                    ->orWhere('registration_snapshot->cascade->jenjang', 'like', "%{$search}%")
                    ->orWhere('registration_snapshot->cascade->jenisPendaftaran', 'like', "%{$search}%")
                    ->orWhere('registration_snapshot->cascade->waktuPerkuliahan', 'like', "%{$search}%");
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
            'cascade' => PmbCascadeSnapshot::fromApplication($application),
        ]);
    }

    public function updateStatus(Request $request, PmbLocalApplication $application): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:submitted,verified,rejected'],
            'review_note' => ['nullable', 'string'],
        ]);

        $before = $application->only(['status', 'review_note', 'reviewed_at', 'reviewed_by']);
        $previousStatus = $application->status;
        $application->update([
            'status' => $payload['status'],
            'review_note' => $payload['review_note'] ?? null,
            'reviewed_at' => in_array($payload['status'], [PmbLocalApplication::STATUS_VERIFIED, PmbLocalApplication::STATUS_REJECTED], true) ? now() : null,
            'reviewed_by' => in_array($payload['status'], [PmbLocalApplication::STATUS_VERIFIED, PmbLocalApplication::STATUS_REJECTED], true) ? $request->user()->id : null,
        ]);
        AuditLogger::record('application_status_updated', 'pmb_local_applications', $application->id, $before, $application->fresh()->only(['status', 'review_note', 'reviewed_at', 'reviewed_by']), $request);

        $application = $application->fresh(['user']);
        $campusSetting = CampusBranding::setting();
        $mailService = app(PmbMailService::class);

        if ($payload['status'] === PmbLocalApplication::STATUS_VERIFIED && $previousStatus !== PmbLocalApplication::STATUS_VERIFIED) {
            $mailService->sendToApplication($application, new ApplicationVerifiedMail($application, $campusSetting));
        }

        if ($payload['status'] === PmbLocalApplication::STATUS_REJECTED && $previousStatus !== PmbLocalApplication::STATUS_REJECTED) {
            $mailService->sendToApplication($application, new ApplicationRejectedMail($application, $campusSetting));
        }

        return redirect()
            ->route('admin.local-applications.show', $application)
            ->with('status', 'Status pendaftaran berhasil diperbarui.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'pendaftar-pmb-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama', 'Email', 'WhatsApp', 'Status', 'Jenjang', 'Kampus', 'Prodi', 'Jenis Pendaftaran', 'Waktu/Kelas', 'Jalur Masuk', 'Gelombang', 'Submitted At']);

            PmbLocalApplication::query()
                ->orderByDesc('submitted_at')
                ->chunk(200, function ($applications) use ($handle): void {
                    foreach ($applications as $application) {
                        $cascade = PmbCascadeSnapshot::fromApplication($application);

                        fputcsv($handle, [
                            $application->name,
                            $application->email,
                            $application->phone,
                            $application->status,
                            $cascade['jenjang'],
                            $cascade['lokasi'],
                            $cascade['programStudi'],
                            $cascade['jenisPendaftaran'],
                            $cascade['waktuPerkuliahan'],
                            $cascade['jalurMasuk'],
                            $cascade['gelombang'],
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
