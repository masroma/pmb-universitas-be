@extends('admin.layout')

@section('title', 'Pendaftaran Dibuka')
@section('page_title', 'Pendaftaran Dibuka')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Program Studi Dibuka</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Pendaftaran Dibuka</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Menampilkan program studi yang dibuka per periode, jalur pendaftaran, dan sistem kuliah dari data lokal SEVIMA.
                    </p>
                </div>
                <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                    <p class="text-xs font-bold uppercase tracking-[0.14em]">Total Data</p>
                    <p class="mt-1 text-3xl font-bold">{{ number_format($totalRecords) }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Pendaftaran Dibuka</h3>
                    <p class="mt-1 text-sm text-slate-500">Filter berdasarkan tahun periode, status, prodi, jalur, atau sistem kuliah.</p>
                </div>
                <form method="GET" action="{{ route('admin.pmb-catalog.opened-registrations') }}" class="grid gap-2 sm:grid-cols-[11rem_11rem_14rem_auto]">
                    <select
                        name="periode_akademik"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua periode</option>
                        @foreach ($periodYears as $periodYear)
                            <option value="{{ $periodYear }}" @selected($selectedPeriodYear === $periodYear)>{{ $periodYear }}</option>
                        @endforeach
                    </select>
                    <select
                        name="status"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua status</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari data..."
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
                            <th class="px-5 py-4">Prodi</th>
                            <th class="px-5 py-4">Periode</th>
                            <th class="px-5 py-4">Jalur</th>
                            <th class="px-5 py-4">Sistem Kuliah</th>
                            <th class="px-5 py-4">Akreditasi</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($records as $record)
                            @php($payload = $record->raw_payload ?? [])
                            @php($periodStatus = $periodStatusBySevimaId[$record->parent_sevima_id] ?? null)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ data_get($payload, 'program_studi', $record->title) ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ data_get($payload, 'jenjang_program_studi', $record->subtitle) ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ data_get($payload, 'nama_periode_pendaftaran') ?: '-' }}</p>
                                    <p class="mt-1 text-xs">{{ data_get($payload, 'periode_akademik') ?: $record->period ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ data_get($payload, 'jalur_pendaftaran') ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ data_get($payload, 'sistem_kuliah') ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ data_get($payload, 'akreditasi') ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        {{ $periodStatus ?: ($record->is_active ? 'Aktif' : 'Nonaktif') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data pendaftaran dibuka.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $records->links() }}
            </div>
        </section>
    </div>
@endsection
