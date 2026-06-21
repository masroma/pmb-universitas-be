@extends('admin.layout')

@section('title', 'Detail Lead AI')
@section('page_title', 'Detail Lead AI')

@section('content')
    @php
        $fieldClass = 'rounded-2xl bg-slate-50 p-4';
        $labelClass = 'text-xs font-bold uppercase tracking-[0.12em] text-slate-400';
        $valueClass = 'mt-2 text-sm font-semibold text-slate-800';
        $qualification = $lead->qualification ?? [];
    @endphp

    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.ai-chat-leads.index') }}" class="text-sm font-bold text-blue-700 transition hover:text-blue-800">
                &larr; Kembali ke CRM AI
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Lead #{{ $lead->id }}</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">{{ $lead->name ?: 'Tanpa Nama' }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        {{ $lead->email ?: '-' }} · {{ $lead->whatsapp ?: '-' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700 ring-1 ring-blue-100">
                        Score {{ $lead->score }}/100
                    </span>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 ring-1 ring-slate-200">
                        {{ $statusLabels[$lead->status] ?? $lead->status }}
                    </span>
                    @if ($lead->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $lead->whatsapp) }}" target="_blank" class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-100">
                            Hubungi WA
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Profil Lead</h3>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Nama</p>
                            <p class="{{ $valueClass }}">{{ $lead->name ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">WhatsApp</p>
                            <p class="{{ $valueClass }}">{{ $lead->whatsapp ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Email</p>
                            <p class="{{ $valueClass }}">{{ $lead->email ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Prodi Minat</p>
                            <p class="{{ $valueClass }}">{{ $lead->study_program_interest ?: '-' }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Transcript Chat</h3>
                    <div class="mt-5 space-y-3">
                        @forelse ($lead->conversation?->messages ?? [] as $message)
                            <div class="{{ $message->role === 'user' ? 'ml-auto bg-blue-700 text-white' : 'bg-slate-50 text-slate-700' }} max-w-3xl whitespace-pre-line rounded-2xl p-4 text-sm leading-6">
                                <p class="mb-2 text-xs font-bold uppercase tracking-[0.12em] {{ $message->role === 'user' ? 'text-blue-100' : 'text-slate-400' }}">
                                    {{ $message->role === 'user' ? 'Calon Mahasiswa' : 'AI PMB' }} · {{ $message->created_at?->format('d M Y H:i') }}
                                </p>
                                {{ $message->content }}
                            </div>
                        @empty
                            <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada transcript chat.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Follow Up PMB</h3>
                    <form method="POST" action="{{ route('admin.ai-chat-leads.follow-up.update', $lead) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="follow_up_status" class="text-sm font-semibold text-slate-700">Status Follow Up</label>
                            <select id="follow_up_status" name="follow_up_status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                @foreach ($followUpLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('follow_up_status', $lead->follow_up_status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="follow_up_note" class="text-sm font-semibold text-slate-700">Catatan Follow Up</label>
                            <textarea id="follow_up_note" name="follow_up_note" rows="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('follow_up_note', $lead->follow_up_note) }}</textarea>
                        </div>
                        <button type="submit" class="w-full rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                            Simpan Follow Up
                        </button>
                    </form>
                    <p class="mt-4 text-xs leading-5 text-slate-500">
                        Terakhir follow up: {{ $lead->followed_up_at?->format('d M Y H:i') ?: '-' }}
                        @if ($lead->followUpUser)
                            oleh {{ $lead->followUpUser->name }}
                        @endif
                    </p>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Qualification</h3>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Status Lead</p>
                            <p class="{{ $valueClass }}">{{ $statusLabels[$lead->status] ?? $lead->status }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Consent</p>
                            <p class="{{ $valueClass }}">{{ $lead->consented_at?->format('d M Y H:i') ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Alasan</p>
                            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm leading-6 text-slate-700">
                                @forelse (($qualification['reasons'] ?? []) as $reason)
                                    <li>{{ $reason }}</li>
                                @empty
                                    <li>Belum ada alasan qualification.</li>
                                @endforelse
                            </ul>
                        </div>
                        <details class="rounded-2xl bg-slate-950 p-4 text-xs text-slate-100">
                            <summary class="cursor-pointer font-bold">Lihat JSON Qualification</summary>
                            <pre class="mt-4 overflow-x-auto whitespace-pre-wrap">{{ json_encode($qualification, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
