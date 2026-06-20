@extends('admin.layout')

@section('title', 'Pendaftar')
@section('page_title', 'Pendaftar')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Data Pendaftar SEVIMA</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Pendaftar</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Data lokal dari endpoint <span class="font-semibold text-slate-700">/siakadcloud/v1/pendaftar</span>, termasuk periode akademik, jalur, sistem kuliah, NIM, dan status daftar ulang.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total Pendaftar</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalApplicants) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Daftar Ulang</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalReRegisteredApplicants) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Pendaftar</h3>
                    <p class="mt-1 text-sm text-slate-500">Filter berdasarkan periode akademik dan status daftar ulang.</p>
                </div>
                <form method="GET" action="{{ route('admin.pmb-catalog.applicants') }}" class="grid gap-2 sm:grid-cols-[11rem_11rem_14rem_auto]">
                    <select
                        name="periode"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua periode</option>
                        @foreach ($periodOptions as $period)
                            <option value="{{ $period }}" @selected($selectedPeriod === $period)>{{ $period }}</option>
                        @endforeach
                    </select>
                    <select
                        name="daftar_ulang"
                        class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">Semua status</option>
                        <option value="yes" @selected($selectedReRegistration === 'yes')>Sudah daftar ulang</option>
                        <option value="no" @selected($selectedReRegistration === 'no')>Belum daftar ulang</option>
                    </select>
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari nama/NIM/email..."
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
                            <th class="px-5 py-4">Periode</th>
                            <th class="px-5 py-4">Jalur / Sistem</th>
                            <th class="px-5 py-4">Kontak</th>
                            <th class="px-5 py-4">Tanggal</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applicants as $applicant)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $applicant->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Kode: {{ $applicant->code ?: '-' }} · NIM: {{ $applicant->nim ?: '-' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p class="font-semibold text-slate-800">{{ $applicant->academic_period_id ?: '-' }}</p>
                                    <p class="mt-1 text-xs">{{ $applicant->registration_period_name ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p>{{ $applicant->registration_path ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $applicant->study_system ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p>{{ $applicant->email ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $applicant->phone ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">
                                    <p>Daftar: {{ $applicant->registered_at?->format('d M Y H:i') ?: '-' }}</p>
                                    <p class="mt-1 text-xs">Daftar ulang: {{ $applicant->re_registered_at?->format('d M Y H:i') ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-2">
                                        <span class="{{ $applicant->is_re_registered ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} w-fit rounded-full px-3 py-1 text-xs font-bold ring-1">
                                            {{ $applicant->is_re_registered ? 'Sudah daftar ulang' : 'Belum daftar ulang' }}
                                        </span>
                                        <span class="{{ $applicant->is_final ? 'bg-blue-50 text-blue-700 ring-blue-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} w-fit rounded-full px-3 py-1 text-xs font-bold ring-1">
                                            {{ $applicant->is_final ? 'Final' : 'Belum final' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data pendaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $applicants->links() }}
            </div>
        </section>
    </div>
@endsection
