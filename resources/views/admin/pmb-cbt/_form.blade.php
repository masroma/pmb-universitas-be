@php
    $options = old('options', $question->options ?? ['A' => '', 'B' => '', 'C' => '', 'D' => '']);
@endphp

<div class="space-y-4">
    <div>
        <label for="category" class="text-sm font-semibold text-slate-700">Kategori</label>
        <select id="category" name="category" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            @foreach ($categories as $key => $label)
                <option value="{{ $key }}" @selected(old('category', $question->category) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="question" class="text-sm font-semibold text-slate-700">Pertanyaan</label>
        <textarea id="question" name="question" rows="4" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('question', $question->question) }}</textarea>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="option_a" class="text-sm font-semibold text-slate-700">Opsi A</label>
            <input id="option_a" name="option_a" type="text" value="{{ old('option_a', $options['A'] ?? '') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
        </div>
        <div>
            <label for="option_b" class="text-sm font-semibold text-slate-700">Opsi B</label>
            <input id="option_b" name="option_b" type="text" value="{{ old('option_b', $options['B'] ?? '') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
        </div>
        <div>
            <label for="option_c" class="text-sm font-semibold text-slate-700">Opsi C</label>
            <input id="option_c" name="option_c" type="text" value="{{ old('option_c', $options['C'] ?? '') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
        </div>
        <div>
            <label for="option_d" class="text-sm font-semibold text-slate-700">Opsi D</label>
            <input id="option_d" name="option_d" type="text" value="{{ old('option_d', $options['D'] ?? '') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="correct_option" class="text-sm font-semibold text-slate-700">Jawaban Benar</label>
            <select id="correct_option" name="correct_option" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                @foreach (['A', 'B', 'C', 'D'] as $option)
                    <option value="{{ $option }}" @selected(old('correct_option', $question->correct_option) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="sort_order" class="text-sm font-semibold text-slate-700">Urutan</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $question->sort_order ?? 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
        </div>
    </div>

    <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
        Soal aktif (ikut diacak ke peserta)
    </label>
</div>
