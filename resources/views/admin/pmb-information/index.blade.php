@extends('admin.layout')

@section('title', 'Konten PMB')
@section('page_title', 'Konten PMB')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Konten Brosur dan Informasi PMB</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Konten PMB</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Kelola informasi tambahan seperti lokasi kampus, jadwal kuliah, syarat masuk, kurikulum, kontak, dan catatan biaya.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-blue-50 px-5 py-4 text-blue-700 ring-1 ring-blue-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalSections) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Aktif</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalActiveSections) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Daftar Konten</h3>
                    <p class="mt-1 text-sm text-slate-500">Filter berdasarkan program, kategori, atau kata kunci.</p>
                </div>
                <div class="flex flex-col gap-3">
                    <form method="GET" action="{{ route('admin.pmb-information.index') }}" class="grid gap-2 sm:grid-cols-[9rem_12rem_14rem_auto]">
                        <select name="program_level" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Semua program</option>
                            @foreach ($programLevels as $programLevel)
                                <option value="{{ $programLevel }}" @selected($selectedProgramLevel === $programLevel)>{{ $programLevel }}</option>
                            @endforeach
                        </select>
                        <select name="category" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $categoryKey => $categoryLabel)
                                <option value="{{ $categoryKey }}" @selected($selectedCategory === $categoryKey)>{{ $categoryLabel }}</option>
                            @endforeach
                        </select>
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari konten..."
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                        <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                            Filter
                        </button>
                    </form>
                    <a href="{{ route('admin.pmb-information.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                        Tambah Konten
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Konten</th>
                            <th class="px-5 py-4">Program</th>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">Poin</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sections as $section)
                            <tr class="align-top">
                                <td class="max-w-xl px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $section->title }}</p>
                                    @if ($section->subtitle)
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $section->subtitle }}</p>
                                    @endif
                                    @if ($section->body)
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $section->body }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">{{ $section->program_level ?: 'Umum' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $categories[$section->category] ?? $section->category }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ count($section->items ?? []) }}</td>
                                <td class="px-5 py-4">
                                    <span class="{{ $section->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                        {{ $section->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                    <p class="mt-2 text-xs text-slate-400">Urutan {{ $section->sort_order }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.pmb-information.edit', array_merge([$section->id], request()->only(['q', 'program_level', 'category', 'page']))) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.pmb-information.destroy', $section->id) }}" onsubmit="return confirm('Hapus konten ini?')">
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
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada konten PMB.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $sections->links() }}
            </div>
        </section>
    </div>
@endsection
