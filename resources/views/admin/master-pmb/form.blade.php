@extends('admin.layout')

@section('title', $config['label'])
@section('page_title', 'Master PMB')

@section('content')
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
        $labelClass = 'text-sm font-semibold text-slate-700';
        $isEdit = filled($record->id ?? null);
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Master PMB Standalone</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">
                        {{ $isEdit ? 'Edit' : 'Tambah' }} {{ $config['label'] }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">Data ini langsung dipakai portal pendaftaran, API, dan AI.</p>
                </div>
                <a href="{{ route('admin.master-pmb.index', $resource) }}" class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-bold">Ada data yang perlu diperbaiki.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('admin.master-pmb.update', [$resource, $record->id]) : route('admin.master-pmb.store', $resource) }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($config['fields'] as $name => $field)
                    @php
                        $type = $field['type'] ?? 'text';
                        $value = old($name, data_get($record, $name));
                    @endphp

                    <div class="{{ in_array($type, ['textarea', 'json_lines'], true) ? 'lg:col-span-2' : '' }}">
                        @if ($type === 'checkbox')
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1" @checked(old($name, (bool) $value)) class="h-5 w-5 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                                <label for="{{ $name }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>
                            </div>
                        @else
                            <label for="{{ $name }}" class="{{ $labelClass }}">{{ $field['label'] }}</label>

                            @if ($type === 'select')
                                @php($options = is_callable($field['options'] ?? null) ? ($field['options'])() : ($field['options'] ?? []))
                                <select id="{{ $name }}" name="{{ $name }}" class="{{ $inputClass }}">
                                    <option value="">Pilih {{ strtolower($field['label']) }}</option>
                                    @foreach ($options as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif ($type === 'textarea' || $type === 'json_lines')
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $type === 'json_lines' ? 8 : 4 }}" class="{{ $inputClass }}">{{ $value }}</textarea>
                                @if ($type === 'json_lines')
                                    <p class="mt-2 text-xs text-slate-500">Tulis satu poin per baris.</p>
                                @endif
                            @else
                                <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" class="{{ $inputClass }}">
                            @endif
                        @endif

                        @error($name)
                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.master-pmb.index', $resource) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
