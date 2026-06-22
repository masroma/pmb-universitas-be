<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbApplicant;
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
        $selectedPeriodYear = $request->string('periode_akademik')->toString();
        $selectedStudyProgram = $request->string('program_studi')->toString();
        $selectedRegistrationPath = $request->string('jalur_pendaftaran')->toString();
        $selectedStatus = $request->string('status')->toString();
        $records = DB::table('pmb_registration_options')
            ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
            ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
            ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->join('admission_paths', 'admission_paths.id', '=', 'pmb_registration_options.admission_path_id')
            ->leftJoin('class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id')
            ->when($selectedPeriodYear !== '', fn ($query) => $query->where('pmb_admission_periods.academic_year', $selectedPeriodYear))
            ->when($selectedStudyProgram !== '', fn ($query) => $query->where('study_programs.name', $selectedStudyProgram))
            ->when($selectedRegistrationPath !== '', fn ($query) => $query->where('admission_paths.name', $selectedRegistrationPath))
            ->when($selectedStatus === 'active', fn ($query) => $query->where('pmb_registration_options.is_active', true))
            ->when($selectedStatus === 'inactive', fn ($query) => $query->where('pmb_registration_options.is_active', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('study_programs.name', 'like', "%{$search}%")
                    ->orWhere('campuses.name', 'like', "%{$search}%")
                    ->orWhere('pmb_admission_periods.name', 'like', "%{$search}%")
                    ->orWhere('pmb_waves.name', 'like', "%{$search}%")
                    ->orWhere('admission_paths.name', 'like', "%{$search}%")
                    ->orWhere('class_types.name', 'like', "%{$search}%");
            }))
            ->select([
                'pmb_registration_options.id',
                'pmb_registration_options.is_active',
                'pmb_admission_periods.name as period_name',
                'pmb_admission_periods.academic_year',
                'pmb_waves.name as wave_name',
                'campuses.name as campus_name',
                'study_programs.name as study_program_name',
                'study_programs.level',
                'study_programs.accreditation',
                'admission_paths.name as path_name',
                'class_types.name as class_name',
            ])
            ->orderBy('pmb_admission_periods.starts_at')
            ->orderBy('pmb_waves.sort_order')
            ->orderBy('campuses.sort_order')
            ->orderBy('study_programs.sort_order')
            ->paginate(25)
            ->withQueryString();

        $periodYears = DB::table('pmb_admission_periods')->pluck('academic_year')->filter()->unique()->sortDesc()->values();
        $studyPrograms = DB::table('study_programs')->pluck('name')->filter()->unique()->sort()->values();
        $registrationPaths = DB::table('admission_paths')->pluck('name')->filter()->unique()->sort()->values();
        $statusOptions = collect(['active', 'inactive']);

        return view('admin.pmb-catalog.opened-registrations', [
            'campusSetting' => $this->campusSetting(),
            'periodYears' => $periodYears,
            'registrationPaths' => $registrationPaths,
            'records' => $records,
            'search' => $search,
            'selectedPeriodYear' => $selectedPeriodYear,
            'selectedRegistrationPath' => $selectedRegistrationPath,
            'selectedStudyProgram' => $selectedStudyProgram,
            'selectedStatus' => $selectedStatus,
            'statusOptions' => $statusOptions,
            'studyPrograms' => $studyPrograms,
            'totalRecords' => $records->total(),
        ]);
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
