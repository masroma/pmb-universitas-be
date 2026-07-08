<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait CreatesStandalonePmbFixtures
{
    /**
     * @return array{
     *     institution_id: int,
     *     campus_id: int,
     *     campus_program_id: int,
     *     study_program_id: int,
     *     path_id: int,
     *     class_type_id: int,
     *     period_id: int,
     *     wave_id: int,
     *     registration_option_id: int
     * }
     */
    protected function createStandalonePmbFixture(array $overrides = []): array
    {
        $now = now();

        $institutionId = DB::table('institutions')->insertGetId([
            'code' => $overrides['institution_code'] ?? 'test-uni',
            'name' => $overrides['institution_name'] ?? 'Universitas Test',
            'short_name' => 'Test',
            'website' => 'https://test.ac.id',
            'description' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $campusId = DB::table('campuses')->insertGetId([
            'institution_id' => $institutionId,
            'code' => 'main',
            'name' => 'Kampus Utama',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'address' => 'Jl. Test',
            'maps_url' => null,
            'is_main' => true,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $facultyId = DB::table('faculties')->insertGetId([
            'institution_id' => $institutionId,
            'code' => 'fisip',
            'name' => 'Fakultas Ilmu Sosial',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studyProgramId = DB::table('study_programs')->insertGetId([
            'institution_id' => $institutionId,
            'faculty_id' => $facultyId,
            'code' => 'komunikasi',
            'level' => 'S1',
            'name' => 'Ilmu Komunikasi',
            'degree' => 'S.I.Kom',
            'accreditation' => 'A',
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $campusProgramId = DB::table('campus_study_programs')->insertGetId([
            'campus_id' => $campusId,
            'study_program_id' => $studyProgramId,
            'is_open' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $classTypeId = DB::table('class_types')->insertGetId([
            'institution_id' => $institutionId,
            'code' => 'reguler',
            'name' => 'Reguler',
            'schedule_label' => 'Pagi',
            'description' => null,
            'is_online' => false,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $periodId = DB::table('pmb_admission_periods')->insertGetId([
            'institution_id' => $institutionId,
            'code' => $overrides['period_code'] ?? '2026-ganjil',
            'name' => $overrides['period_name'] ?? 'PMB 2026 Ganjil',
            'academic_year' => $overrides['academic_year'] ?? '2026',
            'starts_at' => $overrides['period_starts_at'] ?? '2026-01-01',
            'ends_at' => $overrides['period_ends_at'] ?? '2026-12-31',
            'brochure_url' => $overrides['brochure_url'] ?? null,
            'is_active' => $overrides['period_is_active'] ?? true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $waveId = DB::table('pmb_waves')->insertGetId([
            'admission_period_id' => $periodId,
            'code' => $overrides['wave_code'] ?? 'gelombang-1',
            'name' => $overrides['wave_name'] ?? 'Gelombang 1',
            'starts_at' => $overrides['wave_starts_at'] ?? '2026-06-01',
            'ends_at' => $overrides['wave_ends_at'] ?? '2026-06-30',
            'is_active' => $overrides['wave_is_active'] ?? true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pathId = DB::table('admission_paths')->insertGetId([
            'institution_id' => $institutionId,
            'code' => 'reguler',
            'name' => 'Jalur Reguler',
            'description' => 'Jalur pendaftaran reguler',
            'registration_fee' => 300000,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $registrationOptionId = DB::table('pmb_registration_options')->insertGetId([
            'admission_period_id' => $periodId,
            'wave_id' => $waveId,
            'campus_study_program_id' => $campusProgramId,
            'admission_path_id' => $pathId,
            'class_type_id' => $classTypeId,
            'is_active' => $overrides['registration_option_is_active'] ?? true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'institution_id' => $institutionId,
            'campus_id' => $campusId,
            'campus_program_id' => $campusProgramId,
            'study_program_id' => $studyProgramId,
            'path_id' => $pathId,
            'class_type_id' => $classTypeId,
            'period_id' => $periodId,
            'wave_id' => $waveId,
            'registration_option_id' => $registrationOptionId,
        ];
    }
}
