@extends('admin.layout')

@section('title', $config['label'])
@section('page_title', 'Master PMB')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Master PMB Standalone</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">{{ $config['label'] }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Kelola data {{ strtolower($config['label']) }} yang dipakai portal pendaftaran, API, dan AI.
                    </p>
                </div>
                <a href="{{ route('admin.master-pmb.create', $resource) }}" class="inline-flex justify-center rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                    Tambah Data
                </a>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[16rem_1fr]">
            <aside class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="px-2 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Menu Master</p>
                <div class="mt-3 space-y-1">
                    @foreach ($resources as $key => $item)
                        <a href="{{ route('admin.master-pmb.index', $key) }}" class="{{ $resource === $key ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' : 'text-slate-600 hover:bg-slate-50' }} block rounded-2xl px-4 py-3 text-sm font-semibold">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </aside>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">Daftar {{ $config['label'] }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Total data: {{ number_format($records->total()) }}</p>
                    </div>
                    <form method="GET" action="{{ route('admin.master-pmb.index', $resource) }}" class="flex gap-2">
                        <input type="search" name="q" value="{{ $search }}" placeholder="Cari data..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-72">
                        <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                @foreach ($config['columns'] as $label)
                                    <th class="px-5 py-4">{{ $label }}</th>
                                @endforeach
                                <th class="px-5 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($records as $record)
                                <tr class="align-top">
                                    @foreach ($config['columns'] as $column => $label)
                                        @php($value = data_get($record, $column))
                                        <td class="px-5 py-4 text-slate-700">
                                            @if (is_bool($value) || $value === 0 || $value === 1)
                                                <span class="{{ $value ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                                                    {{ $value ? 'Ya' : 'Tidak' }}
                                                </span>
                                            @else
                                                {{ filled($value) ? $value : '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.master-pmb.edit', [$resource, $record->id, ...request()->only(['q', 'page'])]) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.master-pmb.destroy', [$resource, $record->id]) }}" onsubmit="return confirm('Hapus data ini?')">
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
                                    <td colspan="{{ count($config['columns']) + 1 }}" class="px-5 py-10 text-center text-sm text-slate-500">
                                        Belum ada data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 p-5">
                    {{ $records->links() }}
                </div>
            </div>
        </section>
    </div>
@endsection
