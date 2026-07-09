@extends('admin.layout')

@section('title', 'Pendaftaran Dibuka')
@section('page_title', 'Pendaftaran Dibuka')

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Program Studi Dibuka (SEVIMA)</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Pendaftaran Dibuka</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Data sinkron dari SEVIMA untuk form cascade portal: jenjang, prodi, lokasi, jenis pendaftaran, kelas, dan jalur masuk.
                    </p>
                    @if ($lastSyncedAt)
                        <p class="mt-3 text-xs font-semibold text-slate-500">Terakhir disinkronkan: {{ \Illuminate\Support\Carbon::parse($lastSyncedAt)->format('d M Y H:i') }}</p>
                    @endif
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total Data</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalRecords) }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.pmb-catalog.opened-registrations.sync') }}">
                        @csrf
                        <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-4 text-sm font-bold text-white transition hover:bg-slate-800">
                            Sync dari SEVIMA
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-end">
                <form method="GET" action="{{ route('admin.pmb-catalog.opened-registrations') }}" class="grid gap-2 sm:grid-cols-2 xl:grid-cols-[8rem_12rem_10rem_12rem_12rem_14rem_auto]">
                    <select name="jenjang" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Jenjang</option>
                        @foreach ($jenjangOptions as $jenjang)
                            <option value="{{ $jenjang }}" @selected($selectedJenjang === $jenjang)>{{ $jenjang }}</option>
                        @endforeach
                    </select>
                    <select name="program_studi" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Program studi</option>
                        @foreach ($studyPrograms as $studyProgram)
                            <option value="{{ $studyProgram }}" @selected($selectedStudyProgram === $studyProgram)>{{ $studyProgram }}</option>
                        @endforeach
                    </select>
                    <select name="lokasi" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Lokasi</option>
                        @foreach ($lokasiOptions as $lokasi)
                            <option value="{{ $lokasi }}" @selected($selectedLokasi === $lokasi)>{{ $lokasi }}</option>
                        @endforeach
                    </select>
                    <select name="jalur_pendaftaran" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Jalur masuk</option>
                        @foreach ($registrationPaths as $registrationPath)
                            <option value="{{ $registrationPath }}" @selected($selectedRegistrationPath === $registrationPath)>{{ $registrationPath }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Status</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Cari data..." class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Jenjang / Prodi</th>
                            <th class="px-5 py-4">Lokasi</th>
                            <th class="px-5 py-4">Waktu / Kelas</th>
                            <th class="px-5 py-4">Jalur Masuk</th>
                            <th class="px-5 py-4">Gelombang & Biaya</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($records as $record)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $record->program_studi ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $record->jenjang_program_studi ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->lokasi ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->nama_periode_pendaftaran ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $record->jalur_pendaftaran ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $record->gelombang ?: '-' }}</p>
                                    <p class="mt-1 text-xs">Rp{{ number_format((int) $record->registration_fee, 0, ',', '.') }}</p>
                                    @if ($record->tanggal_awal_pendaftaran || $record->tanggal_akhir_pendaftaran)
                                        <p class="mt-1 text-xs text-slate-500">{{ $record->tanggal_awal_pendaftaran }} s/d {{ $record->tanggal_akhir_pendaftaran }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="{{ $record->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $record->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data pendaftaran dibuka. Jalankan sync dari SEVIMA.
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
