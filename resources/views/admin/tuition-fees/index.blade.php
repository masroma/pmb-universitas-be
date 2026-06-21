@extends('admin.layout')

@section('title', 'Biaya Kuliah')
@section('page_title', 'Biaya Kuliah')

@section('content')
    @php
        $rupiah = fn ($amount) => 'Rp' . number_format((int) $amount, 0, ',', '.');
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Master Biaya PMB</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Rincian Biaya Kuliah</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Kelola biaya pendaftaran, nominal angsuran, dan biaya per semester berdasarkan periode, kampus, gelombang, atau program studi.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalTuitionFees) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Aktif</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalActiveTuitionFees) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Biaya</h3>
                    <p class="mt-1 text-sm text-slate-500">Cari berdasarkan program, periode, kampus, gelombang, atau prodi.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <form method="GET" action="{{ route('admin.tuition-fees.index') }}" class="flex gap-2">
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari biaya..."
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-64"
                        >
                        <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                            Cari
                        </button>
                    </form>
                    <a href="{{ route('admin.tuition-fees.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                        Tambah Biaya
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Program</th>
                            <th class="px-5 py-4">Kampus / Gelombang</th>
                            <th class="px-5 py-4">Pendaftaran</th>
                            <th class="px-5 py-4">Angsuran</th>
                            <th class="px-5 py-4">Biaya / Semester</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tuitionFees as $tuitionFee)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $tuitionFee->program_level }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $tuitionFee->period?->name ?: 'Semua periode' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        @if ($tuitionFee->studyProgram)
                                            Khusus {{ $tuitionFee->studyProgram->title }}
                                        @elseif ($tuitionFee->study_program)
                                            Khusus {{ $tuitionFee->study_program }}
                                        @else
                                            Semua prodi
                                        @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $tuitionFee->campus }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $tuitionFee->wave ?: 'Tanpa gelombang' }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">{{ $rupiah($tuitionFee->registration_fee) }}</td>
                                <td class="px-5 py-4 text-slate-700">
                                    <p class="font-semibold">{{ $tuitionFee->installment_count }}x {{ $rupiah($tuitionFee->installment_amount) }}</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">{{ $rupiah($tuitionFee->semester_fee) }}</td>
                                <td class="px-5 py-4">
                                    <span class="{{ $tuitionFee->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $tuitionFee->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                    <p class="mt-2 text-xs text-slate-400">Urutan {{ $tuitionFee->sort_order }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.tuition-fees.edit', array_merge([$tuitionFee], request()->only(['q', 'page']))) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.tuition-fees.destroy', $tuitionFee) }}" onsubmit="return confirm('Hapus data biaya ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-700 ring-1 ring-red-100 transition hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data biaya kuliah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $tuitionFees->links() }}
            </div>
        </section>
    </div>
@endsection
