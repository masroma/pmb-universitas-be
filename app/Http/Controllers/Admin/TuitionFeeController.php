<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbPeriod;
use App\Models\PmbStudyProgram;
use App\Models\TuitionFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TuitionFeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $tuitionFees = DB::table('tuition_fee_schemes')
            ->join('pmb_registration_options', 'pmb_registration_options.id', '=', 'tuition_fee_schemes.registration_option_id')
            ->join('pmb_admission_periods', 'pmb_admission_periods.id', '=', 'pmb_registration_options.admission_period_id')
            ->leftJoin('pmb_waves', 'pmb_waves.id', '=', 'pmb_registration_options.wave_id')
            ->join('campus_study_programs', 'campus_study_programs.id', '=', 'pmb_registration_options.campus_study_program_id')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->join('admission_paths', 'admission_paths.id', '=', 'pmb_registration_options.admission_path_id')
            ->leftJoin('class_types', 'class_types.id', '=', 'pmb_registration_options.class_type_id')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('study_programs.name', 'like', "%{$search}%")
                    ->orWhere('study_programs.level', 'like', "%{$search}%")
                    ->orWhere('campuses.name', 'like', "%{$search}%")
                    ->orWhere('pmb_waves.name', 'like', "%{$search}%")
                    ->orWhere('pmb_admission_periods.name', 'like', "%{$search}%")
                    ->orWhere('admission_paths.name', 'like', "%{$search}%")
                    ->orWhere('class_types.name', 'like', "%{$search}%");
            }))
            ->select([
                'tuition_fee_schemes.*',
                'pmb_admission_periods.name as period_name',
                'pmb_admission_periods.academic_year',
                'pmb_waves.name as wave_name',
                'campuses.name as campus_name',
                'study_programs.level as program_level',
                'study_programs.name as study_program_name',
                'admission_paths.name as path_name',
                'class_types.name as class_name',
            ])
            ->orderBy('pmb_admission_periods.starts_at')
            ->orderBy('pmb_waves.sort_order')
            ->orderBy('campuses.sort_order')
            ->orderBy('study_programs.sort_order')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tuition-fees.index', [
            'campusSetting' => $this->campusSetting(),
            'search' => $search,
            'totalActiveTuitionFees' => DB::table('tuition_fee_schemes')->where('is_active', true)->count(),
            'totalTuitionFees' => DB::table('tuition_fee_schemes')->count(),
            'tuitionFees' => $tuitionFees,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.master-pmb.create', 'tuition-fees');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.master-pmb.create', 'tuition-fees');
    }

    public function edit(TuitionFee $tuitionFee): RedirectResponse
    {
        return redirect()->route('admin.master-pmb.index', 'tuition-fees');
    }

    public function update(Request $request, TuitionFee $tuitionFee): RedirectResponse
    {
        return redirect()->route('admin.master-pmb.index', 'tuition-fees');
    }

    public function destroy(TuitionFee $tuitionFee): RedirectResponse
    {
        return redirect()->route('admin.master-pmb.index', 'tuition-fees');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'pmb_period_id' => ['nullable', 'integer', 'exists:pmb_periods,id'],
            'pmb_study_program_id' => ['nullable', 'integer', 'exists:pmb_study_programs,id'],
            'program_level' => ['required', 'string', 'max:255'],
            'campus' => ['required', 'string', 'max:255'],
            'wave' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'registration_fee' => ['required', 'integer', 'min:0'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:24'],
            'installment_amount' => ['required', 'integer', 'min:0'],
            'semester_fee' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'periodOptions' => PmbPeriod::query()
                ->orderByDesc('is_active')
                ->orderByDesc('sevima_id')
                ->get(['id', 'name', 'short_name', 'academic_year', 'is_active']),
            'studyProgramOptions' => PmbStudyProgram::query()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id', 'level', 'title', 'is_active']),
        ];
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }
}
