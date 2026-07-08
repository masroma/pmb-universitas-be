<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiInternalApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_ai_endpoint_requires_api_key_when_configured(): void
    {
        config(['services.ai_pmb.internal_api_key' => 'secret-key']);

        $this->getJson('/api/ai/biaya')
            ->assertUnauthorized();

        $this->withHeaders(['X-AI-Internal-Key' => 'secret-key'])
            ->getJson('/api/ai/biaya')
            ->assertOk();
    }
}
