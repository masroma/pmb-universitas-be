@extends('admin.layout')

@section('title', 'Kelola CBT')
@section('page_title', 'Kelola CBT')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-indigo-700">Computer Based Test</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Kelola Soal & Pengaturan CBT</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Atur durasi, passing grade, dan bank soal pilihan ganda untuk tes seleksi PMB.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-indigo-50 px-5 py-4 text-indigo-700 ring-1 ring-indigo-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Total Soal</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalQuestions) }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-700 ring-1 ring-emerald-100">
                        <p class="text-xs font-bold uppercase tracking-[0.14em]">Aktif</p>
                        <p class="mt-1 text-3xl font-bold">{{ number_format($totalActiveQuestions) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Pengaturan Tes</h3>
                    <p class="mt-1 text-sm text-slate-500">Berlaku untuk semua sesi CBT peserta.</p>
                </div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $settings->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $settings->is_active ? 'CBT Aktif' : 'CBT Nonaktif' }}
                </span>
            </div>

            <form method="POST" action="{{ route('admin.pmb-cbt.settings.update') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div class="md:col-span-2">
                    <label for="title" class="text-sm font-semibold text-slate-700">Judul Tes</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $settings->title) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label for="duration_minutes" class="text-sm font-semibold text-slate-700">Durasi (menit)</label>
                    <input id="duration_minutes" name="duration_minutes" type="number" min="5" max="180" value="{{ old('duration_minutes', $settings->duration_minutes) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label for="questions_per_attempt" class="text-sm font-semibold text-slate-700">Jumlah Soal per Sesi</label>
                    <input id="questions_per_attempt" name="questions_per_attempt" type="number" min="1" max="100" value="{{ old('questions_per_attempt', $settings->questions_per_attempt) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label for="pass_score" class="text-sm font-semibold text-slate-700">Nilai Minimal Lulus</label>
                    <input id="pass_score" name="pass_score" type="number" min="1" max="100" value="{{ old('pass_score', $settings->pass_score) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label for="max_attempts" class="text-sm font-semibold text-slate-700">Maksimal Percobaan</label>
                    <input id="max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $settings->max_attempts) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="md:col-span-2">
                    <label for="instructions" class="text-sm font-semibold text-slate-700">Petunjuk untuk Peserta</label>
                    <textarea id="instructions" name="instructions" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('instructions', $settings->instructions) }}</textarea>
                </div>
                <div class="md:col-span-2 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $settings->is_active)) class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                        Aktifkan CBT
                    </label>
                    <button type="submit" class="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-indigo-700">AI Generator</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-950">Generate Soal dengan AI</h3>
                    <p class="mt-1 text-sm text-slate-500">AI akan membuat soal pilihan ganda lalu langsung menyimpannya ke bank soal.</p>
                </div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $openaiConfigured ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $openaiConfigured ? 'OpenAI Siap' : 'OPENAI_API_KEY Belum Diisi' }}
                </span>
            </div>

            @unless ($openaiConfigured)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Tambahkan <code class="font-mono">OPENAI_API_KEY</code> di file <code class="font-mono">backend/.env</code>, lalu jalankan ulang server admin.
                </div>
            @endunless

            <form method="POST" action="{{ route('admin.pmb-cbt.generate') }}" class="mt-6 grid gap-4 md:grid-cols-2" id="cbt-ai-generate-form" onsubmit="const btn=this.querySelector('[type=submit]'); if(btn){ btn.disabled=true; btn.innerText='Sedang generate...'; }">
                @csrf
                <div>
                    <label for="ai_category" class="text-sm font-semibold text-slate-700">Kategori</label>
                    <select id="ai_category" name="category" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', 'umum') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ai_count" class="text-sm font-semibold text-slate-700">Jumlah Soal</label>
                    <input id="ai_count" name="count" type="number" min="1" max="10" value="{{ old('count', 5) }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="ai_difficulty" class="text-sm font-semibold text-slate-700">Tingkat Kesulitan</label>
                    <select id="ai_difficulty" name="difficulty" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                        <option value="mudah" @selected(old('difficulty') === 'mudah')>Mudah</option>
                        <option value="sedang" @selected(old('difficulty', 'sedang') === 'sedang')>Sedang</option>
                        <option value="sulit" @selected(old('difficulty') === 'sulit')>Sulit</option>
                    </select>
                </div>
                <div>
                    <label for="ai_topic" class="text-sm font-semibold text-slate-700">Topik (opsional)</label>
                    <input id="ai_topic" name="topic" type="text" value="{{ old('topic') }}" placeholder="Contoh: Pancasila, logika dasar, aljabar" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-2">
                    <button
                        type="submit"
                        @disabled(! $openaiConfigured)
                        class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Generate Soal dengan AI
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Bank Soal</h3>
                    <p class="mt-1 text-sm text-slate-500">Tambah, edit, atau nonaktifkan soal pilihan ganda.</p>
                </div>
                <div class="flex flex-col gap-3">
                    <form method="GET" action="{{ route('admin.pmb-cbt.index') }}" class="grid gap-2 sm:grid-cols-[10rem_10rem_14rem_auto]">
                        <select name="category" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="">Semua status</option>
                            <option value="active" @selected($selectedStatus === 'active')>Aktif</option>
                            <option value="inactive" @selected($selectedStatus === 'inactive')>Nonaktif</option>
                        </select>
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="Cari soal..."
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                        <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                            Filter
                        </button>
                    </form>
                    <a href="{{ route('admin.pmb-cbt.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                        Tambah Soal
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Soal</th>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">Kunci</th>
                            <th class="px-5 py-4">Urutan</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($questions as $question)
                            <tr>
                                <td class="max-w-md px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ \Illuminate\Support\Str::limit($question->question, 120) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        A: {{ \Illuminate\Support\Str::limit($question->options['A'] ?? '-', 40) }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    {{ $categories[$question->category] ?? $question->category }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ $question->correct_option }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $question->sort_order }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $question->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $question->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.pmb-cbt.edit', $question) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.pmb-cbt.destroy', $question) }}" onsubmit="return confirm('Hapus soal ini?')">
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
                                    Belum ada soal. Klik "Tambah Soal" untuk membuat bank soal CBT.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($questions->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $questions->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
