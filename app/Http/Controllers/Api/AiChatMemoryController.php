<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatConversation;
use App\Models\AiChatLead;
use App\Models\AiChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AiChatMemoryController extends Controller
{
    public function ensureConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['nullable', 'string', 'max:255'],
            'visitor' => ['nullable', 'array'],
            'visitor.name' => ['nullable', 'string', 'max:255'],
            'visitor.email' => ['nullable', 'email', 'max:255'],
            'visitor.whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        $conversation = AiChatConversation::query()->firstOrCreate([
            'id' => $validated['conversation_id'] ?? (string) Str::uuid(),
        ]);

        $this->updateVisitorProfile($conversation, $validated['visitor'] ?? []);
        $conversation->touch();

        return response()->json([
            'data' => $this->conversationPayload($conversation->fresh()),
        ]);
    }

    public function messages(Request $request, string $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = $validated['limit'] ?? 8;
        $messages = AiChatMessage::query()
            ->where('ai_chat_conversation_id', $conversationId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiChatMessage $message): array => $this->messagePayload($message))
            ->all();

        return response()->json([
            'data' => $messages,
        ]);
    }

    public function storeMessage(Request $request, string $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['user', 'assistant'])],
            'content' => ['required', 'string'],
        ]);

        $conversation = AiChatConversation::query()->firstOrCreate([
            'id' => $conversationId,
        ]);

        $message = $conversation->messages()->create($validated);
        $conversation->touch();

        return response()->json([
            'data' => $this->messagePayload($message),
        ], 201);
    }

    public function storeLead(Request $request, string $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'visitor' => ['nullable', 'array'],
            'visitor.name' => ['nullable', 'string', 'max:255'],
            'visitor.email' => ['nullable', 'email', 'max:255'],
            'visitor.whatsapp' => ['nullable', 'string', 'max:30'],
            'study_program_interest' => ['nullable', 'string', 'max:255'],
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'string', Rule::in(['warm', 'qualified', 'hot', 'contact_requested'])],
            'qualification' => ['nullable', 'array'],
            'contact_consent' => ['nullable', 'boolean'],
        ]);

        $conversation = AiChatConversation::query()->firstOrCreate([
            'id' => $conversationId,
        ]);
        $this->updateVisitorProfile($conversation, $validated['visitor'] ?? []);

        $consentedAt = ($validated['contact_consent'] ?? false) ? now() : $conversation->contact_consent_at;
        $conversation->fill([
            'lead_status' => $validated['status'],
            'lead_score' => $validated['score'],
            'lead_interest' => $validated['study_program_interest'] ?? $conversation->lead_interest,
            'lead_qualified_at' => now(),
            'contact_consent_at' => $consentedAt,
        ])->save();

        $lead = AiChatLead::query()->updateOrCreate(
            ['ai_chat_conversation_id' => $conversation->id],
            [
                'name' => $conversation->visitor_name,
                'email' => $conversation->visitor_email,
                'whatsapp' => $conversation->visitor_whatsapp,
                'study_program_interest' => $validated['study_program_interest'] ?? $conversation->lead_interest,
                'score' => $validated['score'],
                'status' => $validated['status'],
                'qualification' => $validated['qualification'] ?? [],
                'consented_at' => $consentedAt,
            ],
        );

        return response()->json([
            'data' => $this->leadPayload($lead->fresh()),
        ], 201);
    }

    private function messagePayload(AiChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $message->content,
            'createdAt' => $message->created_at?->toISOString(),
        ];
    }

    private function updateVisitorProfile(AiChatConversation $conversation, array $visitor): void
    {
        $updates = collect([
            'visitor_name' => $visitor['name'] ?? null,
            'visitor_email' => $visitor['email'] ?? null,
            'visitor_whatsapp' => $visitor['whatsapp'] ?? null,
        ])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => filled($value))
            ->all();

        if ($updates !== []) {
            $conversation->fill($updates)->save();
        }
    }

    private function conversationPayload(AiChatConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'visitor' => [
                'name' => $conversation->visitor_name,
                'email' => $conversation->visitor_email,
                'whatsapp' => $conversation->visitor_whatsapp,
            ],
            'lead' => [
                'status' => $conversation->lead_status,
                'score' => $conversation->lead_score,
                'interest' => $conversation->lead_interest,
                'qualifiedAt' => $conversation->lead_qualified_at?->toISOString(),
                'contactConsentAt' => $conversation->contact_consent_at?->toISOString(),
            ],
        ];
    }

    private function leadPayload(AiChatLead $lead): array
    {
        return [
            'id' => $lead->id,
            'conversationId' => $lead->ai_chat_conversation_id,
            'name' => $lead->name,
            'email' => $lead->email,
            'whatsapp' => $lead->whatsapp,
            'studyProgramInterest' => $lead->study_program_interest,
            'score' => $lead->score,
            'status' => $lead->status,
            'qualification' => $lead->qualification ?? [],
            'consentedAt' => $lead->consented_at?->toISOString(),
        ];
    }
}
