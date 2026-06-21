<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbApplicant;
use App\Models\PmbPeriod;
use App\Models\PmbSevimaRecord;
use App\Models\PmbStudyProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $periodIdsByStatus = collect();

        if ($selectedStatus !== '') {
            $periodIdsByStatus = PmbSevimaRecord::query()
                ->where('entity_type', 'periode-pendaftaran')
                ->where('status', $selectedStatus)
                ->pluck('sevima_id');
        }

        $records = PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->when($selectedPeriodYear !== '', fn ($query) => $query->where('raw_payload->periode_akademik', $selectedPeriodYear))
            ->when($selectedStudyProgram !== '', fn ($query) => $query->where(function ($query) use ($selectedStudyProgram): void {
                $query
                    ->where('raw_payload->program_studi', $selectedStudyProgram)
                    ->orWhere('title', $selectedStudyProgram);
            }))
            ->when($selectedRegistrationPath !== '', fn ($query) => $query->where('raw_payload->jalur_pendaftaran', $selectedRegistrationPath))
            ->when($selectedStatus !== '', fn ($query) => $query->whereIn('parent_sevima_id', $periodIdsByStatus))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('period', 'like', "%{$search}%")
                    ->orWhere('raw_payload->nama_periode_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('raw_payload->jalur_pendaftaran', 'like', "%{$search}%")
                    ->orWhere('raw_payload->sistem_kuliah', 'like', "%{$search}%");
            }))
            ->orderBy('parent_sevima_id')
            ->orderBy('title')
            ->paginate(25)
            ->withQueryString();

        $periodYears = PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->get(['raw_payload'])
            ->map(fn (PmbSevimaRecord $record): ?string => filled(data_get($record->raw_payload, 'periode_akademik'))
                ? (string) data_get($record->raw_payload, 'periode_akademik')
                : null)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $studyPrograms = PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->get(['raw_payload', 'title'])
            ->map(fn (PmbSevimaRecord $record): ?string => filled(data_get($record->raw_payload, 'program_studi'))
                ? (string) data_get($record->raw_payload, 'program_studi')
                : $record->title)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $registrationPaths = PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->get(['raw_payload'])
            ->map(fn (PmbSevimaRecord $record): ?string => filled(data_get($record->raw_payload, 'jalur_pendaftaran'))
                ? (string) data_get($record->raw_payload, 'jalur_pendaftaran')
                : null)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $statusOptions = PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->pluck('status')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $periodStatusBySevimaId = PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->pluck('status', 'sevima_id');

        return view('admin.pmb-catalog.opened-registrations', [
            'campusSetting' => $this->campusSetting(),
            'periodStatusBySevimaId' => $periodStatusBySevimaId,
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

        $programs = PmbStudyProgram::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%")
                    ->orWhere('accreditation', 'like', "%{$search}%")
                    ->orWhere('sevima_id', 'like', "%{$search}%");
            }))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pmb-catalog.study-programs', [
            'campusSetting' => $this->campusSetting(),
            'programs' => $programs,
            'search' => $search,
            'totalPrograms' => PmbStudyProgram::query()->count(),
        ]);
    }

    public function periods(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedStatus = $request->string('status')->toString();

        $periods = PmbPeriod::query()
            ->when($selectedStatus === 'active', fn ($query) => $query->where('is_active', true))
            ->when($selectedStatus === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('sevima_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%")
                    ->orWhere('academic_year', 'like', "%{$search}%");
            }))
            ->orderByDesc('sevima_id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pmb-catalog.periods', [
            'campusSetting' => $this->campusSetting(),
            'periods' => $periods,
            'search' => $search,
            'selectedStatus' => $selectedStatus,
            'totalActivePeriods' => PmbPeriod::query()->where('is_active', true)->count(),
            'totalInactivePeriods' => PmbPeriod::query()->where('is_active', false)->count(),
            'totalPeriods' => PmbPeriod::query()->count(),
        ]);
    }

    public function updatePeriodBrochure(Request $request, PmbPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'brochure_path' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($period->brochure_path) {
            Storage::disk('public')->delete($period->brochure_path);
        }

        $period->update([
            'brochure_path' => $validated['brochure_path']->store('pmb/brochures', 'public'),
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
