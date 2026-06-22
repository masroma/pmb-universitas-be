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
                        Menampilkan program studi yang dibuka per periode, cabang kampus, jalur pendaftaran, dan kelas dari data PMB standalone.
                    </p>
                </div>
                <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                    <p class="text-xs font-bold uppercase tracking-[0.14em]">Total Data</p>
                    <p class="mt-1 text-3xl font-bold">{{ number_format($totalRecords) }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-end">
                <form method="GET" action="{{ route('admin.pmb-catalog.opened-registrations') }}" class="grid gap-2 sm:grid-cols-2 xl:grid-cols-[11rem_11rem_14rem_12rem_14rem_auto]">
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
                    <select
                        name="program_studi"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua prodi</option>
                        @foreach ($studyPrograms as $studyProgram)
                            <option value="{{ $studyProgram }}" @selected($selectedStudyProgram === $studyProgram)>{{ $studyProgram }}</option>
                        @endforeach
                    </select>
                    <select
                        name="jalur_pendaftaran"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua jalur</option>
                        @foreach ($registrationPaths as $registrationPath)
                            <option value="{{ $registrationPath }}" @selected($selectedRegistrationPath === $registrationPath)>{{ $registrationPath }}</option>
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
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $record->study_program_name ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $record->level ?: '-' }} · {{ $record->campus_name ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $record->wave_name ?: $record->period_name }}</p>
                                    <p class="mt-1 text-xs">{{ $record->academic_year ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->path_name ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->class_name ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->accreditation ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="{{ $record->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $record->is_active ? 'Aktif' : 'Nonaktif' }}
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
