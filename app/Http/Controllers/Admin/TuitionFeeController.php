<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use App\Models\PmbPeriod;
use App\Models\PmbStudyProgram;
use App\Models\TuitionFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TuitionFeeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $tuitionFees = TuitionFee::query()
            ->with(['period', 'studyProgram'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('program_level', 'like', "%{$search}%")
                    ->orWhere('campus', 'like', "%{$search}%")
                    ->orWhere('wave', 'like', "%{$search}%")
                    ->orWhere('study_program', 'like', "%{$search}%")
                    ->orWhereHas('period', fn ($query) => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('academic_year', 'like', "%{$search}%"))
                    ->orWhereHas('studyProgram', fn ($query) => $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%"));
            }))
            ->orderBy('sort_order')
            ->orderBy('campus')
            ->orderBy('wave')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tuition-fees.index', [
            'campusSetting' => $this->campusSetting(),
            'search' => $search,
            'totalActiveTuitionFees' => TuitionFee::query()->where('is_active', true)->count(),
            'totalTuitionFees' => TuitionFee::query()->count(),
            'tuitionFees' => $tuitionFees,
        ]);
    }

    public function create(): View
    {
        return view('admin.tuition-fees.create', [
            'campusSetting' => $this->campusSetting(),
            ...$this->formOptions(),
            'tuitionFee' => new TuitionFee([
                'program_level' => 'Sarjana',
                'registration_fee' => 300000,
                'installment_count' => 6,
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TuitionFee::query()->create($this->validatedData($request));

        return redirect()
            ->route('admin.tuition-fees.index')
            ->with('status', 'Biaya kuliah berhasil ditambahkan.');
    }

    public function edit(TuitionFee $tuitionFee): View
    {
        return view('admin.tuition-fees.edit', [
            'campusSetting' => $this->campusSetting(),
            ...$this->formOptions(),
            'tuitionFee' => $tuitionFee,
        ]);
    }

    public function update(Request $request, TuitionFee $tuitionFee): RedirectResponse
    {
        $tuitionFee->update($this->validatedData($request));

        return redirect()
            ->route('admin.tuition-fees.index', $request->only(['q', 'page']))
            ->with('status', 'Biaya kuliah berhasil diperbarui.');
    }

    public function destroy(TuitionFee $tuitionFee): RedirectResponse
    {
        $tuitionFee->delete();

        return redirect()
            ->route('admin.tuition-fees.index')
            ->with('status', 'Biaya kuliah berhasil dihapus.');
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
