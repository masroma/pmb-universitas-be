@extends('admin.layout')

@section('title', 'Pendaftaran Lokal')
@section('page_title', 'Pendaftaran Lokal')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Portal Mahasiswa</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Pendaftaran Lokal</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Data pendaftaran yang diisi calon mahasiswa dari portal dan disimpan di database lokal.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalApplications) }}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 px-5 py-4 text-amber-700 ring-1 ring-amber-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Review</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalSubmitted) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Verified</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalVerified) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Pendaftaran</h3>
                    <p class="mt-1 text-sm text-slate-500">Filter berdasarkan status atau cari nama, email, NIK, periode, dan prodi.</p>
                </div>
                <form method="GET" action="{{ route('admin.local-applications.index') }}" class="grid gap-2 sm:grid-cols-[13rem_16rem_auto]">
                    <select
                        name="status"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari pendaftar..."
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
                            <th class="px-5 py-4">Pendaftar</th>
                            <th class="px-5 py-4">Pilihan PMB</th>
                            <th class="px-5 py-4">Kontak</th>
                            <th class="px-5 py-4">Dokumen</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applications as $application)
                            @php
                                $badgeClass = match ($application->status) {
                                    'verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                    'submitted' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                    'rejected' => 'bg-red-50 text-red-700 ring-red-100',
                                    default => 'bg-slate-100 text-slate-600 ring-slate-200',
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $application->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">NIK: {{ $application->nik ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $application->study_program_name ?: '-' }}</p>
                                    <p class="mt-1 text-xs">{{ $application->registration_period_name ?: '-' }}</p>
                                    <p class="mt-1 text-xs">{{ $application->registration_path_name ?: '-' }} / {{ $application->study_system_name ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p>{{ $application->email ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $application->phone ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    {{ $application->documents->count() }} file
                                </td>
                                <td class="px-5 py-4">
                                    <span class="{{ $badgeClass }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $statusLabels[$application->status] ?? $application->status }}
                                    </span>
                                    <p class="mt-2 text-xs text-slate-500">
                                        Submit: {{ $application->submitted_at?->format('d M Y H:i') ?: '-' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.local-applications.show', $application) }}" class="inline-flex rounded-xl bg-blue-700 px-4 py-2 text-xs font-bold text-white transition hover:bg-blue-800">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada pendaftaran lokal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $applications->links() }}
            </div>
        </section>
    </div>
@endsection
