<?php

namespace Tests\Feature;

use App\Models\PmbPeriod;
use App\Models\PmbSevimaRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PmbRegistrationOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_options_only_include_current_active_registration_period_data(): void
    {
        now()->setTestNow('2026-06-20');

        try {
            PmbPeriod::query()->create([
                'sevima_id' => '20261',
                'name' => 'Periode Lama',
                'is_active' => true,
            ]);
            PmbPeriod::query()->create([
                'sevima_id' => '20262',
                'name' => 'Periode Aktif',
                'is_active' => true,
            ]);

            PmbSevimaRecord::query()->create([
                'entity_type' => 'periode-pendaftaran',
                'sevima_id' => 'REG-OLD',
                'title' => 'Gelombang Lama',
                'starts_at' => '2026-05-01',
                'ends_at' => '2026-05-31',
                'is_active' => true,
                'raw_payload' => [
                    'periode_akademik' => '20261',
                ],
            ]);
            PmbSevimaRecord::query()->create([
                'entity_type' => 'periode-pendaftaran',
                'sevima_id' => 'REG-ACTIVE',
                'title' => 'Gelombang Aktif',
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-06-30',
                'is_active' => true,
                'raw_payload' => [
                    'periode_akademik' => '20262',
                ],
            ]);
            PmbSevimaRecord::query()->create([
                'entity_type' => 'periode-pendaftaran',
                'sevima_id' => 'REG-INACTIVE',
                'title' => 'Gelombang Nonaktif',
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-06-30',
                'is_active' => false,
                'raw_payload' => [
                    'periode_akademik' => '20262',
                ],
            ]);

            PmbSevimaRecord::query()->create([
                'entity_type' => 'program-studi-dibuka',
                'parent_type' => 'periode-pendaftaran',
                'parent_sevima_id' => 'REG-OLD',
                'title' => 'Program Lama',
                'is_active' => true,
            ]);
            $activeProgram = PmbSevimaRecord::query()->create([
                'entity_type' => 'program-studi-dibuka',
                'parent_type' => 'periode-pendaftaran',
                'parent_sevima_id' => 'REG-ACTIVE',
                'title' => 'Program Aktif',
                'is_active' => true,
            ]);

            $this->getJson('/api/registration/options')
                ->assertOk()
                ->assertJsonPath('data.academicPeriods.0.id', '20262')
                ->assertJsonPath('data.registrationPeriods.0.id', 'REG-ACTIVE')
                ->assertJsonPath('data.programOptions.0.id', $activeProgram->id)
                ->assertJsonCount(1, 'data.academicPeriods')
                ->assertJsonCount(1, 'data.registrationPeriods')
                ->assertJsonCount(1, 'data.programOptions');
        } finally {
            now()->setTestNow();
        }
    }
}
