@extends('admin.layout')

@section('title', 'Program Studi')
@section('page_title', 'Program Studi')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Master PMB Standalone</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Program Studi</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Daftar program studi yang dipakai website, portal pendaftaran, API, dan AI.
                    </p>
                </div>
                <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                    <p class="text-xs font-bold uppercase tracking-[0.14em]">Total Prodi</p>
                    <p class="mt-1 text-3xl font-bold">{{ number_format($totalPrograms) }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Program Studi</h3>
                    <p class="mt-1 text-sm text-slate-500">Cari berdasarkan nama prodi, jenjang, akreditasi, kode, atau fakultas.</p>
                </div>
                <form method="GET" action="{{ route('admin.pmb-catalog.study-programs') }}" class="flex gap-2">
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Cari prodi..."
                        class="w-56 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                    <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                        Cari
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Kode</th>
                            <th class="px-5 py-4">Program Studi</th>
                            <th class="px-5 py-4">Fakultas</th>
                            <th class="px-5 py-4">Jenjang</th>
                            <th class="px-5 py-4">Akreditasi</th>
                            <th class="px-5 py-4">Urutan</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($programs as $program)
                            <tr>
                                <td class="px-5 py-4 text-slate-500">{{ $program->code ?: '-' }}</td>
                                <td class="px-5 py-4 font-bold text-slate-950">{{ $program->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $program->faculty_name ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $program->level ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $program->accreditation ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $program->sort_order }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        {{ $program->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data program studi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $programs->links() }}
            </div>
        </section>
    </div>
@endsection
