@extends('admin.layout')

@section('title', 'CRM AI')
@section('page_title', 'CRM AI')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">AI Lead Qualification</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Lead dari Chat AI</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Data calon mahasiswa yang terdeteksi berminat tinggi dari percakapan AI PMB.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalLeads) }}</p>
                    </div>
                    <div class="rounded-2xl bg-red-50 px-5 py-4 text-red-700 ring-1 ring-red-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Hot</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalHot) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Minta Kontak</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalContactRequested) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Lead</h3>
                    <p class="mt-1 text-sm text-slate-500">Filter berdasarkan lead status, follow up, atau cari kontak dan prodi minat.</p>
                </div>
                <div class="flex flex-col gap-2 xl:flex-row">
                <form method="GET" action="{{ route('admin.ai-chat-leads.index') }}" class="grid gap-2 lg:grid-cols-[12rem_13rem_16rem_auto]">
                    <select name="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua lead</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="follow_up_status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua follow up</option>
                        @foreach ($followUpLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedFollowUpStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari lead..." class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                        Filter
                    </button>
                </form>
                <a href="{{ route('admin.ai-chat-leads.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                    Export CSV
                </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Lead</th>
                            <th class="px-5 py-4">Minat</th>
                            <th class="px-5 py-4">Score</th>
                            <th class="px-5 py-4">Follow Up</th>
                            <th class="px-5 py-4">Masuk</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leads as $lead)
                            @php
                                $statusClass = match ($lead->status) {
                                    'hot', 'contact_requested' => 'bg-red-50 text-red-700 ring-red-100',
                                    'qualified' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                    default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $lead->name ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $lead->email ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $lead->whatsapp ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $lead->study_program_interest ?: '-' }}</p>
                                    <span class="{{ $statusClass }} mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $statusLabels[$lead->status] ?? $lead->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-2xl font-bold text-slate-950">{{ $lead->score }}</p>
                                    <p class="text-xs text-slate-500">/ 100</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $followUpLabels[$lead->follow_up_status] ?? $lead->follow_up_status ?? 'Baru' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $lead->followed_up_at?->format('d M Y H:i') ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $lead->created_at?->format('d M Y H:i') ?: '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.ai-chat-leads.show', $lead) }}" class="inline-flex rounded-xl bg-blue-700 px-4 py-2 text-xs font-bold text-white transition hover:bg-blue-800">
                                            Detail
                                        </a>
                                        @if ($lead->whatsapp)
                                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $lead->whatsapp) }}" target="_blank" class="inline-flex rounded-xl bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-100">
                                                WhatsApp
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada lead dari AI.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $leads->links() }}
            </div>
        </section>
    </div>
@endsection
