<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiConsentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_consent_is_persisted(): void
    {
        $conversationId = $this->postJson('/api/ai/memory/conversations', [
            'visitor' => ['name' => 'Andi', 'whatsapp' => '+628777777777'],
        ])->json('data.id');

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", [
            'visitor' => ['name' => 'Andi', 'whatsapp' => '+628777777777'],
            'score' => 88,
            'status' => 'contact_requested',
            'contact_consent' => true,
            'qualification_key' => 'consent-turn',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'contact_requested')
            ->assertJsonPath('data.name', 'Andi');

        $this->assertDatabaseHas('ai_chat_conversations', [
            'id' => $conversationId,
            'lead_status' => 'contact_requested',
        ]);
    }
}
