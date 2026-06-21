<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TuitionFee;
use Illuminate\Http\JsonResponse;

class TuitionFeeController extends Controller
{
    public function index(): JsonResponse
    {
        $tuitionFees = TuitionFee::query()
            ->with(['period', 'studyProgram'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('pmb_period_id')
            ->orderBy('campus')
            ->orderBy('wave')
            ->orderBy('pmb_study_program_id')
            ->orderBy('study_program')
            ->get()
            ->map(fn (TuitionFee $tuitionFee): array => [
                'id' => $tuitionFee->id,
                'periodId' => $tuitionFee->pmb_period_id,
                'period' => $tuitionFee->period ? [
                    'id' => $tuitionFee->period->id,
                    'name' => $tuitionFee->period->name,
                    'shortName' => $tuitionFee->period->short_name,
                    'academicYear' => $tuitionFee->period->academic_year,
                    'isActive' => $tuitionFee->period->is_active,
                ] : null,
                'programLevel' => $tuitionFee->program_level,
                'campus' => $tuitionFee->campus,
                'wave' => $tuitionFee->wave,
                'studyProgramId' => $tuitionFee->pmb_study_program_id,
                'studyProgram' => $tuitionFee->studyProgram?->title ?? $tuitionFee->study_program,
                'studyProgramDetail' => $tuitionFee->studyProgram ? [
                    'id' => $tuitionFee->studyProgram->id,
                    'level' => $tuitionFee->studyProgram->level,
                    'title' => $tuitionFee->studyProgram->title,
                    'accreditation' => $tuitionFee->studyProgram->accreditation,
                    'isActive' => $tuitionFee->studyProgram->is_active,
                ] : null,
                'registrationFee' => $tuitionFee->registration_fee,
                'installmentCount' => $tuitionFee->installment_count,
                'installmentAmount' => $tuitionFee->installment_amount,
                'semesterFee' => $tuitionFee->semester_fee,
            ])
            ->all();

        return response()->json([
            'data' => $tuitionFees,
        ]);
    }
}
