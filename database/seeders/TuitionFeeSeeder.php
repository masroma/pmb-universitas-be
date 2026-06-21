<?php

namespace Database\Seeders;

use App\Models\PmbStudyProgram;
use App\Models\TuitionFee;
use Illuminate\Database\Seeder;

class TuitionFeeSeeder extends Seeder
{
    public function run(): void
    {
        $registrationFee = 300000;
        $falsafahStudyProgramId = PmbStudyProgram::query()
            ->where('title', 'like', '%Falsafah%')
            ->where('title', 'like', '%Agama%')
            ->value('id');

        $tuitionFees = [
            [
                'program_level' => 'Sarjana',
                'campus' => 'Kampus Cipayung',
                'wave' => 'Gelombang 1',
                'pmb_period_id' => null,
                'pmb_study_program_id' => null,
                'study_program' => null,
                'installment_amount' => 1450000,
                'semester_fee' => 8700000,
                'sort_order' => 10,
            ],
            [
                'program_level' => 'Sarjana',
                'campus' => 'Kampus Cipayung',
                'wave' => 'Gelombang 2',
                'pmb_period_id' => null,
                'pmb_study_program_id' => null,
                'study_program' => null,
                'installment_amount' => 1550000,
                'semester_fee' => 9300000,
                'sort_order' => 20,
            ],
            [
                'program_level' => 'Sarjana',
                'campus' => 'Kampus Cipayung',
                'wave' => null,
                'pmb_period_id' => null,
                'pmb_study_program_id' => $falsafahStudyProgramId,
                'study_program' => 'Falsafah dan Agama',
                'installment_amount' => 413000,
                'semester_fee' => 2475000,
                'sort_order' => 30,
            ],
            [
                'program_level' => 'Sarjana',
                'campus' => 'Kampus Cikarang',
                'wave' => 'Gelombang 1',
                'pmb_period_id' => null,
                'pmb_study_program_id' => null,
                'study_program' => null,
                'installment_amount' => 1150000,
                'semester_fee' => 6900000,
                'sort_order' => 40,
            ],
            [
                'program_level' => 'Sarjana',
                'campus' => 'Kampus Cikarang',
                'wave' => 'Gelombang 2',
                'pmb_period_id' => null,
                'pmb_study_program_id' => null,
                'study_program' => null,
                'installment_amount' => 1250000,
                'semester_fee' => 7500000,
                'sort_order' => 50,
            ],
        ];

        foreach ($tuitionFees as $tuitionFee) {
            TuitionFee::query()->updateOrCreate(
                [
                    'program_level' => $tuitionFee['program_level'],
                    'campus' => $tuitionFee['campus'],
                    'wave' => $tuitionFee['wave'],
                    'study_program' => $tuitionFee['study_program'],
                ],
                [
                    'pmb_period_id' => $tuitionFee['pmb_period_id'],
                    'pmb_study_program_id' => $tuitionFee['pmb_study_program_id'],
                    'registration_fee' => $registrationFee,
                    'installment_count' => 6,
                    'installment_amount' => $tuitionFee['installment_amount'],
                    'semester_fee' => $tuitionFee['semester_fee'],
                    'is_active' => true,
                    'sort_order' => $tuitionFee['sort_order'],
                ],
            );
        }
    }
}
