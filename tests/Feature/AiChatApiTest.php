<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_api_proxies_request_and_returns_ai_response(): void
    {
        config(['services.ai_pmb.url' => 'http://ai-service.test/chat']);
        Http::fake([
            'http://ai-service.test/chat' => Http::response([
                'answer' => 'Informasi biaya tersedia.',
                'intent' => 'biaya',
                'sources' => ['pmb-universitas:/api/ai/biaya'],
                'metadata' => ['conversationId' => 'conv-1'],
            ], 200),
        ]);

        $response = $this->postJson('/api/ai/chat', [
            'message' => 'Berapa biaya kuliah?',
            'conversation_id' => 'conv-1',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.intent', 'biaya')
            ->assertJsonPath('data.metadata.conversationId', 'conv-1');
    }
}
