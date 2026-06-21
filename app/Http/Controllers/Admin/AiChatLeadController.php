<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLead;
use App\Models\CampusSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AiChatLeadController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $selectedStatus = $request->string('status')->toString();
        $selectedFollowUpStatus = $request->string('follow_up_status')->toString();

        $leads = AiChatLead::query()
            ->with('conversation')
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->when($selectedFollowUpStatus !== '', fn ($query) => $query->where('follow_up_status', $selectedFollowUpStatus))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('study_program_interest', 'like', "%{$search}%");
            }))
            ->orderByDesc('score')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.ai-chat-leads.index', [
            'campusSetting' => $this->campusSetting(),
            'followUpLabels' => $this->followUpLabels(),
            'leads' => $leads,
            'search' => $search,
            'selectedFollowUpStatus' => $selectedFollowUpStatus,
            'selectedStatus' => $selectedStatus,
            'statusLabels' => $this->statusLabels(),
            'totalContactRequested' => AiChatLead::query()->where('status', 'contact_requested')->count(),
            'totalHot' => AiChatLead::query()->where('status', 'hot')->count(),
            'totalLeads' => AiChatLead::query()->count(),
        ]);
    }

    public function show(AiChatLead $lead): View
    {
        $lead->load([
            'conversation.messages' => fn ($query) => $query->orderBy('id'),
            'followUpUser',
        ]);

        return view('admin.ai-chat-leads.show', [
            'campusSetting' => $this->campusSetting(),
            'followUpLabels' => $this->followUpLabels(),
            'lead' => $lead,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function updateFollowUp(Request $request, AiChatLead $lead): RedirectResponse
    {
        $payload = $request->validate([
            'follow_up_status' => ['required', Rule::in(array_keys($this->followUpLabels()))],
            'follow_up_note' => ['nullable', 'string'],
        ]);

        $lead->update([
            'follow_up_status' => $payload['follow_up_status'],
            'follow_up_note' => $payload['follow_up_note'] ?? null,
            'followed_up_at' => now(),
            'followed_up_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.ai-chat-leads.show', $lead)
            ->with('status', 'Status follow up lead berhasil diperbarui.');
    }

    private function campusSetting(): CampusSetting
    {
        return CampusSetting::query()->first() ?? CampusSetting::query()->create([
            'campus_name' => 'Universitas',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function statusLabels(): array
    {
        return [
            'warm' => 'Warm',
            'qualified' => 'Qualified',
            'hot' => 'Hot',
            'contact_requested' => 'Minta Dihubungi',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function followUpLabels(): array
    {
        return [
            AiChatLead::FOLLOW_UP_NEW => 'Baru',
            AiChatLead::FOLLOW_UP_CONTACTED => 'Sudah Dihubungi',
            AiChatLead::FOLLOW_UP_INTERESTED => 'Tertarik',
            AiChatLead::FOLLOW_UP_REGISTERED => 'Sudah Daftar',
            AiChatLead::FOLLOW_UP_NOT_INTERESTED => 'Tidak Tertarik',
        ];
    }
}
