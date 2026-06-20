@extends('admin.layout')

@section('title', 'Periode Akademik')
@section('page_title', 'Periode')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Periode Akademik SEVIMA</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Periode</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Daftar periode akademik dari endpoint <span class="font-semibold text-slate-700">/siakadcloud/v1/periode</span> yang disimpan di database lokal.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalPeriods) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Aktif</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalActivePeriods) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-5 py-4 text-slate-700 ring-1 ring-slate-200">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Tidak Aktif</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalInactivePeriods) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Periode</h3>
                    <p class="mt-1 text-sm text-slate-500">Cari berdasarkan ID, nama periode, nama singkat, atau tahun ajar.</p>
                </div>
                <form method="GET" action="{{ route('admin.pmb-catalog.periods') }}" class="grid gap-2 sm:grid-cols-[11rem_14rem_auto]">
                    <select
                        name="status"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua status</option>
                        <option value="active" @selected($selectedStatus === 'active')>Aktif</option>
                        <option value="inactive" @selected($selectedStatus === 'inactive')>Tidak Aktif</option>
                    </select>
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari periode..."
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                    <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">SEVIMA ID</th>
                            <th class="px-5 py-4">Periode</th>
                            <th class="px-5 py-4">Tahun Ajar</th>
                            <th class="px-5 py-4">Tanggal Akademik</th>
                            <th class="px-5 py-4">UTS</th>
                            <th class="px-5 py-4">UAS</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Brosur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($periods as $period)
                            <tr class="align-top">
                                <td class="px-5 py-4 text-slate-500">{{ $period->sevima_id ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $period->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $period->short_name ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $period->academic_year ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $period->starts_at?->format('d M Y') ?: '-' }}
                                    <span class="text-slate-300">s/d</span>
                                    {{ $period->ends_at?->format('d M Y') ?: '-' }}
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $period->midterm_starts_at?->format('d M Y') ?: '-' }}
                                    <span class="text-slate-300">s/d</span>
                                    {{ $period->midterm_ends_at?->format('d M Y') ?: '-' }}
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $period->final_starts_at?->format('d M Y') ?: '-' }}
                                    <span class="text-slate-300">s/d</span>
                                    {{ $period->final_ends_at?->format('d M Y') ?: '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="{{ $period->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $period->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="min-w-72 px-5 py-4">
                                    <div class="space-y-3">
                                        @if ($period->brochure_url)
                                            <a href="{{ $period->brochure_url }}" target="_blank" class="inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100">
                                                Lihat Brosur
                                            </a>
                                        @else
                                            <p class="text-xs font-semibold text-slate-400">Belum ada brosur.</p>
                                        @endif

                                        <form method="POST" action="{{ route('admin.pmb-catalog.periods.brochure.update', $period) }}" enctype="multipart/form-data" class="space-y-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="q" value="{{ $search }}">
                                            <input type="hidden" name="status" value="{{ $selectedStatus }}">
                                            <input type="hidden" name="page" value="{{ $periods->currentPage() }}">
                                            <input
                                                name="brochure_path"
                                                type="file"
                                                accept=".pdf,.doc,.docx,image/*"
                                                required
                                                class="block w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-600 outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-slate-700 hover:border-blue-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            >
                                            <button type="submit" class="rounded-xl bg-blue-700 px-4 py-2 text-xs font-bold text-white transition hover:bg-blue-800">
                                                Upload
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data periode.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $periods->links() }}
            </div>
        </section>
    </div>
@endsection
