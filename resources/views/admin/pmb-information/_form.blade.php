@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
    $labelClass = 'text-sm font-semibold text-slate-700';
    $itemsText = old('items_text');

    if ($itemsText === null) {
        $itemsText = implode(PHP_EOL, $section->items ?? []);
    }
@endphp

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

<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <div class="flex flex-col gap-2 border-b border-slate-100 pb-6">
        <p class="text-sm font-bold text-blue-700">Konten Informasi PMB</p>
        <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Data Konten</h2>
        <p class="max-w-2xl text-sm leading-6 text-slate-500">
            Gunakan poin per baris untuk informasi berbentuk daftar seperti lokasi, jadwal, syarat, atau link.
        </p>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div>
            <label for="program_level" class="{{ $labelClass }}">Program</label>
            <select id="program_level" name="program_level" class="{{ $inputClass }}">
                @foreach ($programLevels as $programLevel)
                    <option value="{{ $programLevel }}" @selected(old('program_level', $section->program_level) === $programLevel)>{{ $programLevel }}</option>
                @endforeach
            </select>
            @error('program_level')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="{{ $labelClass }}">Kategori</label>
            <select id="category" name="category" class="{{ $inputClass }}">
                @foreach ($categories as $categoryKey => $categoryLabel)
                    <option value="{{ $categoryKey }}" @selected(old('category', $section->category) === $categoryKey)>{{ $categoryLabel }}</option>
                @endforeach
            </select>
            @error('category')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="title" class="{{ $labelClass }}">Judul</label>
            <input id="title" name="title" type="text" value="{{ old('title', $section->title) }}" required class="{{ $inputClass }}">
            @error('title')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subtitle" class="{{ $labelClass }}">Subjudul</label>
            <input id="subtitle" name="subtitle" type="text" value="{{ old('subtitle', $section->subtitle) }}" class="{{ $inputClass }}">
            @error('subtitle')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="body" class="{{ $labelClass }}">Deskripsi</label>
            <textarea id="body" name="body" rows="5" class="{{ $inputClass }}">{{ old('body', $section->body) }}</textarea>
            @error('body')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="items_text" class="{{ $labelClass }}">Poin Informasi</label>
            <textarea id="items_text" name="items_text" rows="8" class="{{ $inputClass }}">{{ $itemsText }}</textarea>
            <p class="mt-2 text-xs text-slate-500">Tulis satu poin per baris.</p>
            @error('items_text')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="{{ $labelClass }}">Urutan Tampil</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', $section->sort_order) }}" required class="{{ $inputClass }}">
            @error('sort_order')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked($errors->any() ? old('is_active') : $section->is_active) class="h-5 w-5 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
            <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif dan tampilkan konten ini</label>
        </div>
    </div>
</section>
