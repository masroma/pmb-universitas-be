<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiRaceConditionProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_message_is_idempotent_by_client_message_id(): void
    {
        $conversationId = $this->postJson('/api/ai/memory/conversations', [])->json('data.id');
        $payload = [
            'role' => 'user',
            'content' => 'Saya ingin info biaya.',
            'client_message_id' => 'same-turn:user',
        ];

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/messages", $payload)->assertCreated();
        $this->postJson("/api/ai/memory/conversations/{$conversationId}/messages", $payload)->assertCreated();

        $this->assertDatabaseCount('ai_chat_messages', 1);
    }
}
