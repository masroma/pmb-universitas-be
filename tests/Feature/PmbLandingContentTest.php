<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesStandalonePmbFixtures;
use Tests\TestCase;

class PmbLandingContentTest extends TestCase
{
    use CreatesStandalonePmbFixtures;
    use RefreshDatabase;

    public function test_brochure_url_uses_current_active_academic_period(): void
    {
        $this->createStandalonePmbFixture([
            'period_code' => 'future-period',
            'period_name' => 'Periode Mendatang',
            'academic_year' => '2027',
            'period_starts_at' => '2026-09-21',
            'period_ends_at' => '2027-01-09',
            'brochure_url' => '/storage/pmb/brochures/future.pdf',
            'period_is_active' => false,
        ]);

        $this->createStandalonePmbFixture([
            'institution_code' => 'test-uni-2',
            'period_code' => 'active-period',
            'period_name' => 'Periode Aktif',
            'academic_year' => '2026',
            'period_starts_at' => '2026-03-30',
            'period_ends_at' => '2026-07-04',
            'brochure_url' => '/storage/pmb/brochures/active.pdf',
            'period_is_active' => true,
        ]);

        $this->getJson('/api/landing-content')
            ->assertOk()
            ->assertJsonPath('data.brochureUrl', '/storage/pmb/brochures/active.pdf');
    }
}
