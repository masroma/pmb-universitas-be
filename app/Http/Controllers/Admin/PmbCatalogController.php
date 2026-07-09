<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbApplicant;
use App\Models\PmbOpenStudyProgram;
use App\Services\PmbRegistrationCascadeSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PmbCatalogController extends Controller
{
    public function openedRegistrations(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedJenjang = $request->string('jenjang')->toString();
        $selectedStudyProgram = $request->string('program_studi')->toString();
        $selectedLokasi = $request->string('lokasi')->toString();
        $selectedRegistrationPath = $request->string('jalur_pendaftaran')->toString();
        $selectedStatus = $request->string('status')->toString();

        $records = PmbOpenStudyProgram::query()
            ->leftJoin('pmb_synced_registration_periods', 'pmb_synced_registration_periods.sevima_id', '=', 'pmb_open_study_programs.id_periode_pendaftaran')
            ->when($selectedJenjang !== '', fn ($query) => $query->where('pmb_open_study_programs.jenjang_program_studi', $selectedJenjang))
            ->when($selectedStudyProgram !== '', fn ($query) => $query->where('pmb_open_study_programs.program_studi', $selectedStudyProgram))
            ->when($selectedLokasi !== '', fn ($query) => $query->where('pmb_open_study_programs.lokasi', $selectedLokasi))
            ->when($selectedRegistrationPath !== '', fn ($query) => $query->where('pmb_open_study_programs.jalur_pendaftaran', $selectedRegistrationPath))
            ->when($selectedStatus === 'active', fn ($query) => $query->where('pmb_open_study_programs.is_active', true))
            ->when($selectedStatus === 'inactive', fn ($query) => $query->where('pmb_open_study_programs.is_active', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('pmb_open_study_programs.program_studi', 'like', "%{$search}%")
                    ->orWhere('pmb_open_study_programs.lokasi', 'like', "%{$search}%")
                    ->orWhere('pmb_open_study_programs.jalur_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('pmb_open_study_programs.nama_periode_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('pmb_open_study_programs.sistem_kuliah', 'like', "%{$search}%")
                    ->orWhere('pmb_open_study_programs.gelombang', 'like', "%{$search}%");
            }))
            ->select([
                'pmb_open_study_programs.id',
                'pmb_open_study_programs.jenjang_program_studi',
                'pmb_open_study_programs.program_studi',
                'pmb_open_study_programs.lokasi',
                'pmb_open_study_programs.nama_periode_pendaftaran',
                'pmb_open_study_programs.jalur_pendaftaran',
                'pmb_open_study_programs.gelombang',
                'pmb_open_study_programs.registration_fee',
                'pmb_open_study_programs.is_active',
                'pmb_open_study_programs.synced_at',
                'pmb_synced_registration_periods.tanggal_awal_pendaftaran',
                'pmb_synced_registration_periods.tanggal_akhir_pendaftaran',
            ])
            ->orderBy('pmb_open_study_programs.jenjang_program_studi')
            ->orderBy('pmb_open_study_programs.program_studi')
            ->orderBy('pmb_open_study_programs.lokasi')
            ->orderBy('pmb_open_study_programs.nama_periode_pendaftaran')
            ->paginate(25)
            ->withQueryString();

        $jenjangOptions = PmbOpenStudyProgram::query()->whereNotNull('jenjang_program_studi')->distinct()->orderBy('jenjang_program_studi')->pluck('jenjang_program_studi');
        $studyPrograms = PmbOpenStudyProgram::query()->whereNotNull('program_studi')->distinct()->orderBy('program_studi')->pluck('program_studi');
        $lokasiOptions = PmbOpenStudyProgram::query()->whereNotNull('lokasi')->distinct()->orderBy('lokasi')->pluck('lokasi');
        $registrationPaths = PmbOpenStudyProgram::query()->whereNotNull('jalur_pendaftaran')->distinct()->orderBy('jalur_pendaftaran')->pluck('jalur_pendaftaran');
        $statusOptions = collect(['active', 'inactive']);
        $lastSyncedAt = PmbOpenStudyProgram::query()->max('synced_at');

        return view('admin.pmb-catalog.opened-registrations', [
            'campusSetting' => $this->campusSetting(),
            'jenjangOptions' => $jenjangOptions,
            'lokasiOptions' => $lokasiOptions,
            'registrationPaths' => $registrationPaths,
            'records' => $records,
            'search' => $search,
            'selectedJenjang' => $selectedJenjang,
            'selectedLokasi' => $selectedLokasi,
            'selectedRegistrationPath' => $selectedRegistrationPath,
            'selectedStudyProgram' => $selectedStudyProgram,
            'selectedStatus' => $selectedStatus,
            'statusOptions' => $statusOptions,
            'studyPrograms' => $studyPrograms,
            'totalRecords' => $records->total(),
            'lastSyncedAt' => $lastSyncedAt,
        ]);
    }

    public function syncOpenedRegistrations(): RedirectResponse
    {
        $counts = app(PmbRegistrationCascadeSyncService::class)->syncFromSevimaRecords();

        return redirect()
            ->route('admin.pmb-catalog.opened-registrations')
            ->with('status', 'Sinkronisasi selesai: '.$counts['open_programs'].' program dibuka, '.$counts['periods'].' periode.');
    }

    public function studyPrograms(Request $request): View
    {
        $search = $request->string('q')->toString();

        $programs = DB::table('study_programs')
            ->leftJoin('faculties', 'faculties.id', '=', 'study_programs.faculty_id')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('study_programs.name', 'like', "%{$search}%")
                    ->orWhere('study_programs.level', 'like', "%{$search}%")
                    ->orWhere('study_programs.accreditation', 'like', "%{$search}%")
                    ->orWhere('study_programs.code', 'like', "%{$search}%")
                    ->orWhere('faculties.name', 'like', "%{$search}%");
            }))
            ->select('study_programs.*', 'faculties.name as faculty_name')
            ->orderBy('study_programs.sort_order')
            ->orderBy('study_programs.name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pmb-catalog.study-programs', [
            'campusSetting' => $this->campusSetting(),
            'programs' => $programs,
            'search' => $search,
            'totalPrograms' => DB::table('study_programs')->count(),
        ]);
    }

    public function periods(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedStatus = $request->string('status')->toString();

        $periods = DB::table('pmb_admission_periods')
            ->when($selectedStatus === 'active', fn ($query) => $query->where('is_active', true))
            ->when($selectedStatus === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('academic_year', 'like', "%{$search}%");
            }))
            ->orderByDesc('starts_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pmb-catalog.periods', [
            'campusSetting' => $this->campusSetting(),
            'periods' => $periods,
            'search' => $search,
            'selectedStatus' => $selectedStatus,
            'totalActivePeriods' => DB::table('pmb_admission_periods')->where('is_active', true)->count(),
            'totalInactivePeriods' => DB::table('pmb_admission_periods')->where('is_active', false)->count(),
            'totalPeriods' => DB::table('pmb_admission_periods')->count(),
        ]);
    }

    public function updatePeriodBrochure(Request $request, int $period): RedirectResponse
    {
        $validated = $request->validate([
            'brochure_path' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $path = $validated['brochure_path']->store('pmb/brochures', 'public');

        DB::table('pmb_admission_periods')->where('id', $period)->update([
            'brochure_url' => Storage::disk('public')->url($path),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.pmb-catalog.periods', $request->only(['q', 'status', 'page']))
            ->with('status', 'Brosur periode berhasil diperbarui.');
    }

    public function applicants(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedPeriod = $request->string('periode')->toString();
        $selectedReRegistration = $request->string('daftar_ulang')->toString();

        $applicants = PmbApplicant::query()
            ->when($selectedPeriod !== '', fn ($query) => $query->where('academic_period_id', $selectedPeriod))
            ->when($selectedReRegistration === 'yes', fn ($query) => $query->where('is_re_registered', true))
            ->when($selectedReRegistration === 'no', fn ($query) => $query->where('is_re_registered', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('sevima_id', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('registration_period_name', 'like', "%{$search}%")
                    ->orWhere('study_system', 'like', "%{$search}%")
                    ->orWhere('registration_path', 'like', "%{$search}%");
            }))
            ->latest('registered_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $periodOptions = PmbApplicant::query()
            ->whereNotNull('academic_period_id')
            ->select('academic_period_id')
            ->distinct()
            ->orderByDesc('academic_period_id')
            ->pluck('academic_period_id');

        return view('admin.pmb-catalog.applicants', [
            'applicants' => $applicants,
            'campusSetting' => $this->campusSetting(),
            'periodOptions' => $periodOptions,
            'search' => $search,
            'selectedPeriod' => $selectedPeriod,
            'selectedReRegistration' => $selectedReRegistration,
            'totalApplicants' => PmbApplicant::query()->count(),
            'totalReRegisteredApplicants' => PmbApplicant::query()->where('is_re_registered', true)->count(),
        ]);
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }
}
