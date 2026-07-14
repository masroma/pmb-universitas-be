@extends('admin.layout')

@section('title', 'Dashboard Admin PMB')
@section('page_title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-br from-[#0C1222] via-[#141C2E] to-[#1C2740] p-6 text-white shadow-[0_18px_50px_-28px_rgba(7,11,20,0.45)] sm:p-8">
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-[#C4A574]/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-1/3 h-40 w-40 rounded-full bg-white/5 blur-2xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#E8D5B5]">Funnel PMB AI</p>
                    <h2 class="mt-2 font-display text-4xl font-semibold tracking-[-0.03em] text-white">Dashboard {{ $campusSetting->campus_name }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/65">Pantau performa chat AI, lead CRM, akun calon mahasiswa, dan pendaftaran yang masuk.</p>
                </div>
                <a href="{{ route('admin.master-pmb.index', 'campuses') }}" class="inline-flex w-fit items-center justify-center rounded-2xl bg-gradient-to-r from-[#E8D5B5] to-[#C4A574] px-5 py-3 text-sm font-bold text-[#0C1222] shadow-[0_16px_40px_-20px_rgba(196,165,116,0.9)] transition hover:brightness-105">
                    Kelola Master PMB
                </a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Lead AI', 'value' => $stats['aiLeads'], 'tone' => 'blue'],
                ['label' => 'Hot / Minta Kontak', 'value' => $stats['hotLeads'], 'tone' => 'red'],
                ['label' => 'Akun Calon Mahasiswa', 'value' => $stats['studentAccounts'], 'tone' => 'slate'],
                ['label' => 'Pendaftar Verified', 'value' => $stats['verifiedApplications'], 'tone' => 'emerald'],
            ] as $card)
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-3 text-4xl font-black tracking-[-0.04em] text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Funnel Pendaftaran</h3>
                <div class="mt-5 space-y-3">
                    @foreach ([
                        'Draft' => $stats['draftApplications'],
                        'Submitted / Review' => $stats['submittedApplications'],
                        'Verified' => $stats['verifiedApplications'],
                        'Rejected / Revisi' => $stats['rejectedApplications'],
                    ] as $label => $value)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-600">{{ $label }}</span>
                            <span class="text-lg font-bold text-slate-950">{{ number_format($value) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Follow Up CRM</h3>
                <div class="mt-5 space-y-3">
                    @foreach (['new' => 'Baru', 'contacted' => 'Sudah Dihubungi', 'interested' => 'Tertarik', 'registered' => 'Sudah Daftar', 'not_interested' => 'Tidak Tertarik'] as $status => $label)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-600">{{ $label }}</span>
                            <span class="text-lg font-bold text-slate-950">{{ number_format((int) ($leadFollowUps[$status] ?? 0)) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Pendaftar per Lokasi</h3>
                <div class="mt-5 space-y-3">
                    @forelse ($applicationsByCampus as $row)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-600">{{ $row->campus_name }}</span>
                            <span class="text-lg font-bold text-slate-950">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada pendaftar.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Pendaftar per Prodi</h3>
                <div class="mt-5 space-y-3">
                    @forelse ($applicationsByProgram as $row)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-600">{{ $row->study_program_name }}</span>
                            <span class="text-lg font-bold text-slate-950">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada pendaftar.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
