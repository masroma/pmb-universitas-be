<?php

namespace Tests\Feature;

use App\Models\AiChatLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiSaveLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_lead_updates_existing_record(): void
    {
        $conversationResponse = $this->postJson('/api/ai/memory/conversations', []);
        $conversationResponse->assertOk();
        $conversationId = $conversationResponse->json('data.id');

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", [
            'visitor' => ['name' => 'Budi', 'whatsapp' => '+628123456789'],
            'score' => 72,
            'status' => 'qualified',
            'contact_consent' => false,
            'qualification_key' => 'turn-1',
        ])->assertCreated();

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", [
            'visitor' => ['name' => 'Budi', 'whatsapp' => '+628123456789'],
            'score' => 90,
            'status' => 'hot',
            'contact_consent' => false,
            'qualification_key' => 'turn-2',
        ])->assertCreated();

        $this->assertDatabaseCount('ai_chat_leads', 1);
        $this->assertSame(90, AiChatLead::query()->firstOrFail()->score);
    }

    public function test_save_lead_is_idempotent_for_same_qualification_key(): void
    {
        $conversationId = $this->postJson('/api/ai/memory/conversations', [])->json('data.id');

        $payload = [
            'visitor' => ['name' => 'Siti', 'whatsapp' => '+628111111111'],
            'score' => 80,
            'status' => 'qualified',
            'contact_consent' => false,
            'qualification_key' => 'same-turn',
        ];

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", $payload)->assertCreated();
        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", $payload)->assertCreated();

        $this->assertDatabaseCount('ai_chat_leads', 1);
    }
}
