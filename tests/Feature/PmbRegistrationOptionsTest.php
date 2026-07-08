<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesStandalonePmbFixtures;
use Tests\TestCase;

class PmbRegistrationOptionsTest extends TestCase
{
    use CreatesStandalonePmbFixtures;
    use RefreshDatabase;

    public function test_options_only_include_active_registration_data(): void
    {
        $active = $this->createStandalonePmbFixture([
            'period_code' => 'active-period',
            'wave_code' => 'active-wave',
            'wave_name' => 'Gelombang Aktif',
            'registration_option_is_active' => true,
        ]);

        $inactive = $this->createStandalonePmbFixture([
            'institution_code' => 'inactive-uni',
            'period_code' => 'inactive-period',
            'wave_code' => 'inactive-wave',
            'wave_name' => 'Gelombang Nonaktif',
            'registration_option_is_active' => false,
            'wave_is_active' => false,
            'period_is_active' => false,
        ]);

        $this->getJson('/api/registration/options')
            ->assertOk()
            ->assertJsonPath('data.academicPeriods.0.id', $active['period_id'])
            ->assertJsonPath('data.registrationPeriods.0.id', $active['wave_id'])
            ->assertJsonPath('data.programOptions.0.id', $active['registration_option_id'])
            ->assertJsonCount(1, 'data.academicPeriods')
            ->assertJsonCount(1, 'data.registrationPeriods')
            ->assertJsonCount(1, 'data.programOptions')
            ->assertJsonMissing(['id' => $inactive['period_id']])
            ->assertJsonMissing(['id' => $inactive['registration_option_id']]);
    }

    public function test_options_exclude_inactive_program_options_in_same_period(): void
    {
        $fixture = $this->createStandalonePmbFixture();

        DB::table('pmb_registration_options')->insert([
            'admission_period_id' => $fixture['period_id'],
            'wave_id' => $fixture['wave_id'],
            'campus_study_program_id' => $fixture['campus_program_id'],
            'admission_path_id' => $fixture['path_id'],
            'class_type_id' => $fixture['class_type_id'],
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/registration/options')
            ->assertOk()
            ->assertJsonCount(1, 'data.programOptions');
    }
}
