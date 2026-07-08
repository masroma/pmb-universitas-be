<?php

namespace Tests\Feature;

use App\Models\AiChatLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCrmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_saved_from_memory_endpoint_is_available_in_crm_model_relation(): void
    {
        $conversationId = $this->postJson('/api/ai/memory/conversations', [
            'visitor' => ['name' => 'Rina', 'email' => 'rina@example.com', 'whatsapp' => '+628999999999'],
        ])->json('data.id');

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", [
            'visitor' => ['name' => 'Rina', 'email' => 'rina@example.com', 'whatsapp' => '+628999999999'],
            'study_program_interest' => 'Psikologi',
            'score' => 85,
            'status' => 'hot',
            'contact_consent' => false,
            'qualification_key' => 'crm-turn',
        ])->assertCreated();

        $lead = AiChatLead::query()->with('conversation')->firstOrFail();
        $this->assertSame('hot', $lead->status);
        $this->assertSame('Rina', $lead->conversation?->visitor_name);
        $this->assertSame('Psikologi', $lead->study_program_interest);
    }
}
