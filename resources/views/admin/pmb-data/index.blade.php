@extends('admin.layout')

@section('title', 'Data PMB SEVIMA')
@section('page_title', 'Data PMB')

@section('content')
    <div class="space-y-6">
        @if ($errors->has('sync'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                {{ $errors->first('sync') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">SEVIMA Data PMB</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Migrasi Data PMB ke Lokal</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Data dari endpoint PMB disimpan di database lokal agar admin dan website tidak bergantung langsung ke API SEVIMA setiap request.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.pmb-data.sync') }}" class="flex flex-col gap-3 rounded-2xl bg-slate-50 p-4 sm:flex-row sm:items-center">
                    @csrf
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                        <input type="checkbox" name="no_details" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                        Sync cepat
                    </label>
                    <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                        Sync dari SEVIMA
                    </button>
                </form>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($summary as $type => $total)
                <a href="{{ route('admin.pmb-data.index', ['entity_type' => $type]) }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ $type }}</p>
                    <p class="mt-3 text-3xl font-bold tracking-[-0.04em] text-slate-950">{{ number_format($total) }}</p>
                    <p class="mt-1 text-sm text-slate-500">data lokal</p>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500 sm:col-span-2 xl:col-span-4">
                    Belum ada data PMB lokal. Klik tombol sync untuk menarik data dari SEVIMA.
                </div>
            @endforelse
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Record Lokal</h3>
                    <p class="mt-1 text-sm text-slate-500">Menampilkan data hasil sync dari collection Data PMB.</p>
                </div>

                <form method="GET" action="{{ route('admin.pmb-data.index') }}" class="flex gap-2">
                    <select name="entity_type" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua data</option>
                        @foreach ($entityTypes as $type)
                            <option value="{{ $type }}" @selected($entityType === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Jenis</th>
                            <th class="px-5 py-4">SEVIMA ID</th>
                            <th class="px-5 py-4">Judul</th>
                            <th class="px-5 py-4">Parent</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Sync</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($records as $record)
                            <tr class="align-top">
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $record->entity_type }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $record->sevima_id ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $record->title ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $record->subtitle ?: $record->period ?: $record->amount ?: '' }}</p>
                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-xs font-bold text-blue-700">Lihat payload</summary>
                                        <pre class="mt-2 max-h-64 overflow-auto rounded-2xl bg-slate-950 p-4 text-xs leading-5 text-slate-100">{{ json_encode($record->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    @if ($record->parent_type)
                                        <span class="font-semibold text-slate-700">{{ $record->parent_type }}</span><br>
                                        {{ $record->parent_sevima_id ?: '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $record->status ?: ($record->is_active ? 'aktif' : 'nonaktif') }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    {{ $record->synced_at?->format('d M Y H:i') ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada data untuk filter ini.
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
