<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatPipelineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_memory_intent_recommendation_scoring_and_save_lead_flow(): void
    {
        $conversationId = $this->postJson('/api/ai/memory/conversations', [])->json('data.id');

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/messages", [
            'role' => 'user',
            'content' => 'Saya kerja dan ingin rekomendasi prodi dengan biaya terjangkau',
            'client_message_id' => 'turn-1:user',
        ])->assertCreated();

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/messages", [
            'role' => 'assistant',
            'content' => 'Baik, saya bantu rekomendasi program studi.',
            'client_message_id' => 'turn-1:assistant',
        ])->assertCreated();

        $this->getJson("/api/ai/memory/conversations/{$conversationId}/messages?limit=10")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->postJson("/api/ai/memory/conversations/{$conversationId}/lead", [
            'visitor' => ['name' => 'Nina', 'whatsapp' => '+628888888888'],
            'study_program_interest' => 'Teknik Informatika',
            'score' => 92,
            'status' => 'hot',
            'qualification' => ['source' => 'integration-test'],
            'contact_consent' => false,
            'qualification_key' => 'turn-1',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'hot')
            ->assertJsonPath('data.studyProgramInterest', 'Teknik Informatika');
    }
}
