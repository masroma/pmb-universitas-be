@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
    $labelClass = 'text-sm font-semibold text-slate-700';
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
        <p class="text-sm font-bold text-blue-700">Rincian Biaya Kuliah</p>
        <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Data Biaya</h2>
        <p class="max-w-2xl text-sm leading-6 text-slate-500">
            Masukkan nominal dalam angka rupiah tanpa titik atau simbol Rp. Contoh: 300000.
        </p>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div>
            <label for="pmb_period_id" class="{{ $labelClass }}">Periode</label>
            <select id="pmb_period_id" name="pmb_period_id" class="{{ $inputClass }}">
                <option value="">Semua periode</option>
                @foreach ($periodOptions as $period)
                    <option value="{{ $period->id }}" @selected((string) old('pmb_period_id', $tuitionFee->pmb_period_id) === (string) $period->id)>
                        {{ $period->name }}{{ $period->academic_year ? ' - ' . $period->academic_year : '' }}{{ $period->is_active ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('pmb_period_id')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="pmb_study_program_id" class="{{ $labelClass }}">Program Studi</label>
            <select id="pmb_study_program_id" name="pmb_study_program_id" class="{{ $inputClass }}">
                <option value="">Semua program studi</option>
                @foreach ($studyProgramOptions as $studyProgram)
                    <option value="{{ $studyProgram->id }}" @selected((string) old('pmb_study_program_id', $tuitionFee->pmb_study_program_id) === (string) $studyProgram->id)>
                        {{ $studyProgram->title }}{{ $studyProgram->level ? ' - ' . $studyProgram->level : '' }}{{ $studyProgram->is_active ? '' : ' (Tidak aktif)' }}
                    </option>
                @endforeach
            </select>
            @error('pmb_study_program_id')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="program_level" class="{{ $labelClass }}">Program</label>
            <input id="program_level" name="program_level" type="text" value="{{ old('program_level', $tuitionFee->program_level) }}" required class="{{ $inputClass }}">
            @error('program_level')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="campus" class="{{ $labelClass }}">Kampus</label>
            <input id="campus" name="campus" type="text" value="{{ old('campus', $tuitionFee->campus) }}" required placeholder="Kampus Cipayung" class="{{ $inputClass }}">
            @error('campus')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="wave" class="{{ $labelClass }}">Gelombang</label>
            <input id="wave" name="wave" type="text" value="{{ old('wave', $tuitionFee->wave) }}" placeholder="Gelombang 1" class="{{ $inputClass }}">
            @error('wave')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="study_program" class="{{ $labelClass }}">Khusus Prodi</label>
            <input id="study_program" name="study_program" type="text" value="{{ old('study_program', $tuitionFee->study_program) }}" placeholder="Falsafah dan Agama" class="{{ $inputClass }}">
            @error('study_program')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="registration_fee" class="{{ $labelClass }}">Biaya Pendaftaran</label>
            <input id="registration_fee" name="registration_fee" type="number" min="0" value="{{ old('registration_fee', $tuitionFee->registration_fee) }}" required class="{{ $inputClass }}">
            @error('registration_fee')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="installment_count" class="{{ $labelClass }}">Jumlah Angsuran</label>
            <input id="installment_count" name="installment_count" type="number" min="1" max="24" value="{{ old('installment_count', $tuitionFee->installment_count) }}" required class="{{ $inputClass }}">
            @error('installment_count')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="installment_amount" class="{{ $labelClass }}">Nominal Angsuran</label>
            <input id="installment_amount" name="installment_amount" type="number" min="0" value="{{ old('installment_amount', $tuitionFee->installment_amount) }}" required class="{{ $inputClass }}">
            @error('installment_amount')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="semester_fee" class="{{ $labelClass }}">Biaya per Semester</label>
            <input id="semester_fee" name="semester_fee" type="number" min="0" value="{{ old('semester_fee', $tuitionFee->semester_fee) }}" required class="{{ $inputClass }}">
            @error('semester_fee')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="{{ $labelClass }}">Urutan Tampil</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', $tuitionFee->sort_order) }}" required class="{{ $inputClass }}">
            @error('sort_order')
                <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked($errors->any() ? old('is_active') : $tuitionFee->is_active) class="h-5 w-5 rounded border-slate-300 text-blue-700 focus:ring-blue-500">
            <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif dan tampilkan biaya ini</label>
        </div>
    </div>
</section>
