<?php

namespace Tests\Feature;

use App\Models\PmbPeriod;
use App\Models\PmbSevimaRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PmbLandingContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_brochure_url_uses_current_active_academic_period(): void
    {
        Storage::fake('public');
        config(['sevima.periode_akademik' => '20261']);
        now()->setTestNow('2026-06-20');

        try {
            PmbPeriod::query()->create([
                'sevima_id' => '20261',
                'name' => 'Periode Tidak Aktif',
                'starts_at' => '2026-09-21',
                'ends_at' => '2027-01-09',
                'is_active' => false,
                'brochure_path' => 'pmb/brochures/future.pdf',
            ]);

            PmbPeriod::query()->create([
                'sevima_id' => '20252',
                'name' => 'Periode Aktif',
                'starts_at' => '2026-03-30',
                'ends_at' => '2026-07-04',
                'is_active' => true,
                'brochure_path' => 'pmb/brochures/active.pdf',
            ]);

            PmbSevimaRecord::query()->create([
                'entity_type' => 'periode-pendaftaran',
                'sevima_id' => 'REG-20262',
                'title' => 'Gelombang Aktif',
                'starts_at' => '2026-06-01',
                'ends_at' => '2026-06-30',
                'is_active' => true,
                'raw_payload' => [
                    'periode_akademik' => '20261',
                ],
            ]);

            $this->getJson('/api/landing-content')
                ->assertOk()
                ->assertJsonPath('data.brochureUrl', '/storage/pmb/brochures/active.pdf');
        } finally {
            now()->setTestNow();
        }
    }
}
