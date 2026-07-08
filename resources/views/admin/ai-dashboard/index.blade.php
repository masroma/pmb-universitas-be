@extends('admin.layout')

@section('title', 'Dashboard AI')
@section('page_title', 'Dashboard AI')

@section('content')
    @php
        $maxWeeklyTotal = max(1, (int) $weeklyTrend->max('total'));
        $growthTone = $summary['growthRate'] >= 0 ? 'text-emerald-700 bg-emerald-50 ring-emerald-100' : 'text-red-700 bg-red-50 ring-red-100';
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-200">AI Management Insight</p>
                    <h2 class="mt-2 text-3xl font-black tracking-[-0.04em]">Dashboard AI {{ $campusSetting->campus_name }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-300">
                        Prediksi pendaftar, sinyal lead, dan risiko putus kuliah dihitung dari data pendaftaran lokal, lead AI, dan data sinkron SEVIMA.
                    </p>
                </div>
                <div class="rounded-3xl bg-white/10 px-5 py-4 ring-1 ring-white/10">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-300">Confidence</p>
                    <p class="mt-1 text-3xl font-black">{{ $summary['confidence'] }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Prediksi 30 Hari</p>
                <p class="mt-3 text-4xl font-black tracking-[-0.04em] text-slate-950">{{ number_format($summary['forecastNext30Days']) }}</p>
                <p class="mt-2 text-sm text-slate-500">Estimasi pendaftar baru.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Pendaftar 30 Hari</p>
                <p class="mt-3 text-4xl font-black tracking-[-0.04em] text-slate-950">{{ number_format($summary['currentRegistrations']) }}</p>
                <span class="{{ $growthTone }} mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1">
                    {{ $summary['growthRate'] >= 0 ? '+' : '' }}{{ $summary['growthRate'] }}% vs periode lalu
                </span>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Lead Siap Follow Up</p>
                <p class="mt-3 text-4xl font-black tracking-[-0.04em] text-slate-950">{{ number_format($summary['leadPipeline']) }}</p>
                <p class="mt-2 text-sm text-slate-500">Qualified, hot, atau minta kontak.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Konversi Estimasi</p>
                <p class="mt-3 text-4xl font-black tracking-[-0.04em] text-slate-950">{{ $summary['conversionRate'] }}%</p>
                <p class="mt-2 text-sm text-slate-500">Verified dan daftar ulang dari total basis data.</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">Prediksi Pendaftar per Minggu</h3>
                        <p class="mt-1 text-sm text-slate-500">Tren gabungan pendaftaran lokal dan SEVIMA selama 8 minggu terakhir.</p>
                    </div>
                </div>
                <div class="mt-6 space-y-4">
                    @foreach ($weeklyTrend as $week)
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-600">{{ $week['label'] }}</span>
                                <span class="font-bold text-slate-950">{{ number_format($week['total']) }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-blue-600" style="width: {{ max(5, ($week['total'] / $maxWeeklyTotal) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Rekomendasi Follow Up AI</h3>
                <p class="mt-1 text-sm text-slate-500">Lead prioritas berdasarkan skor dan status dari percakapan AI.</p>
                <div class="mt-5 space-y-3">
                    @forelse ($leadRecommendations as $lead)
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-slate-950">{{ $lead['name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $lead['interest'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">{{ $lead['contact'] }}</p>
                                </div>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100">{{ $lead['score'] }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">Belum ada lead prioritas.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Prediksi Pendaftar per Prodi</h3>
                <p class="mt-1 text-sm text-slate-500">Dihitung dari minat pendaftar lokal 60 hari terakhir.</p>
                <div class="mt-5 space-y-3">
                    @forelse ($programPredictions as $program)
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="font-bold text-slate-950">{{ $program['name'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $program['signal'] }} dari {{ number_format($program['total']) }} data minat.</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-black text-slate-950">{{ number_format($program['forecast']) }}</p>
                                <p class="text-xs text-slate-500">estimasi/bulan</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">Belum ada data prodi untuk diprediksi.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-950">Risiko Putus Kuliah</h3>
                <p class="mt-1 text-sm text-slate-500">Sinyal awal dari aktivasi, finalisasi, daftar ulang, NIM, dan umur pendaftaran.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-red-50 px-4 py-4 text-red-700 ring-1 ring-red-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Tinggi</p>
                        <p class="mt-1 text-3xl font-black">{{ number_format($riskSummary['high']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 px-4 py-4 text-amber-700 ring-1 ring-amber-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Sedang</p>
                        <p class="mt-1 text-3xl font-black">{{ number_format($riskSummary['medium']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-4 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Rendah</p>
                        <p class="mt-1 text-3xl font-black">{{ number_format($riskSummary['low']) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h3 class="text-lg font-bold text-slate-950">Daftar Prioritas Risiko</h3>
                <p class="mt-1 text-sm text-slate-500">Gunakan daftar ini untuk follow up calon mahasiswa/mahasiswa yang berisiko tidak lanjut.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">Jalur / Sistem</th>
                            <th class="px-5 py-4">Risiko</th>
                            <th class="px-5 py-4">Sinyal</th>
                            <th class="px-5 py-4">Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($riskRows as $row)
                            @php
                                $riskClass = match ($row['level']) {
                                    'Tinggi' => 'bg-red-50 text-red-700 ring-red-100',
                                    'Sedang' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                    default => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $row['name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $row['code'] }} · {{ $row['phone'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $row['path'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $row['studySystem'] }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="{{ $riskClass }} inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1">{{ $row['level'] }}</span>
                                    <p class="mt-2 text-xs font-semibold text-slate-500">Score {{ $row['score'] }}/100</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ implode(', ', $row['signals']) }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $row['registeredAt']?->format('d M Y') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada data risiko.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
