@extends('admin.layout')

@section('title', 'Detail Pendaftaran Lokal')
@section('page_title', 'Detail Pendaftaran')

@section('content')
    @php
        $badgeClass = match ($application->status) {
            'verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'submitted' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'rejected' => 'bg-red-50 text-red-700 ring-red-100',
            default => 'bg-slate-100 text-slate-600 ring-slate-200',
        };
        $fieldClass = 'rounded-2xl bg-slate-50 p-4';
        $labelClass = 'text-xs font-bold uppercase tracking-[0.12em] text-slate-400';
        $valueClass = 'mt-2 text-sm font-semibold text-slate-800';
        $snapshot = $application->registration_snapshot ?? [];
        $rupiah = fn ($amount) => 'Rp' . number_format((int) $amount, 0, ',', '.');
    @endphp

    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.local-applications.index') }}" class="text-sm font-bold text-blue-700 transition hover:text-blue-800">
                &larr; Kembali ke daftar
            </a>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-700">Pendaftaran #{{ $application->id }}</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">{{ $application->name }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        {{ $application->email ?: '-' }} · {{ $application->phone ?: '-' }}
                    </p>
                </div>
                <span class="{{ $badgeClass }} w-fit rounded-full px-4 py-2 text-sm font-bold ring-1">
                    {{ $statusLabels[$application->status] ?? $application->status }}
                </span>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Pilihan Pendaftaran</h3>
                    <p class="mt-1 text-sm text-slate-500">Urutan pilihan mengikuti form portal: jenjang → prodi → lokasi → jenis → kelas → jalur.</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Jenjang</p>
                            <p class="{{ $valueClass }}">{{ $cascade['jenjang'] ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Program Studi</p>
                            <p class="{{ $valueClass }}">{{ $cascade['programStudi'] ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Lokasi Kampus</p>
                            <p class="{{ $valueClass }}">{{ $cascade['lokasi'] ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Jenis Pendaftaran</p>
                            <p class="{{ $valueClass }}">{{ $cascade['jenisPendaftaran'] ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Waktu Perkuliahan / Kelas</p>
                            <p class="{{ $valueClass }}">{{ $cascade['waktuPerkuliahan'] ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Jalur Masuk</p>
                            <p class="{{ $valueClass }}">{{ $cascade['jalurMasuk'] ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Gelombang</p>
                            <p class="{{ $valueClass }}">{{ $cascade['gelombang'] ?: $application->registration_period_name ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Periode Akademik</p>
                            <p class="{{ $valueClass }}">{{ $application->academic_period_name ?: $application->academic_period_id ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }} md:col-span-2">
                            <p class="{{ $labelClass }}">Biaya & Periode Pendaftaran</p>
                            <p class="{{ $valueClass }}">
                                Daftar {{ $rupiah($cascade['registrationFee'] ?? data_get($snapshot, 'registrationFee', 0)) }}
                                @if ($cascade['registrationStartsAt'] || $cascade['registrationEndsAt'])
                                    · {{ $cascade['registrationStartsAt'] ?: '?' }} s/d {{ $cascade['registrationEndsAt'] ?: '?' }}
                                @endif
                            </p>
                            @if (data_get($snapshot, 'installmentCount'))
                                <p class="mt-2 text-xs text-slate-500">
                                    Angsuran {{ data_get($snapshot, 'installmentCount', '-') }}x {{ $rupiah(data_get($snapshot, 'installmentAmount', 0)) }}
                                    · Semester {{ $rupiah(data_get($snapshot, 'semesterFee', 0)) }}
                                </p>
                            @endif
                        </div>
                        @if ($cascade['openStudyProgramId'])
                            <div class="{{ $fieldClass }} md:col-span-2">
                                <p class="{{ $labelClass }}">Referensi Data SEVIMA</p>
                                <p class="{{ $valueClass }}">Open Study Program ID: {{ $cascade['openStudyProgramId'] }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Biodata</h3>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Nama</p>
                            <p class="{{ $valueClass }}">{{ $application->name }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">NIK</p>
                            <p class="{{ $valueClass }}">{{ $application->nik ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Jenis Kelamin</p>
                            <p class="{{ $valueClass }}">{{ $application->gender ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Tempat/Tanggal Lahir</p>
                            <p class="{{ $valueClass }}">{{ $application->birth_place ?: '-' }} / {{ $application->birth_date?->format('d M Y') ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Kota / Provinsi</p>
                            <p class="{{ $valueClass }}">{{ $application->city ?: '-' }} / {{ $application->province ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }}">
                            <p class="{{ $labelClass }}">Negara</p>
                            <p class="{{ $valueClass }}">{{ $application->country ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }} md:col-span-2">
                            <p class="{{ $labelClass }}">Alamat</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application->address ?: '-' }}</p>
                        </div>
                        <div class="{{ $fieldClass }} md:col-span-2">
                            <p class="{{ $labelClass }}">Catatan Pendaftar</p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">{{ $application->applicant_note ?: '-' }}</p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Pembayaran Formulir</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Ubah status pembayaran biaya formulir pendaftaran setelah pendaftar melakukan pembayaran.
                    </p>
                    <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-400">Tagihan</p>
                        <p class="mt-2 text-lg font-bold text-slate-900">{{ $rupiah($application->form_payment_amount ?? data_get($snapshot, 'registrationFee', 0)) }}</p>
                        <p class="mt-2 text-sm text-slate-600">
                            Status saat ini:
                            <span class="font-bold {{ ($application->form_payment_status ?? 'pending') === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $formPaymentLabels[$application->form_payment_status ?? 'pending'] ?? 'Belum Bayar' }}
                            </span>
                        </p>
                        @if ($application->form_paid_at)
                            <p class="mt-2 text-xs text-slate-500">Diverifikasi: {{ $application->form_paid_at->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.local-applications.form-payment.update', $application) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="form_payment_status" class="text-sm font-semibold text-slate-700">Status Pembayaran</label>
                            <select id="form_payment_status" name="form_payment_status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="pending" @selected(($application->form_payment_status ?? 'pending') === 'pending')>Belum Bayar</option>
                                <option value="paid" @selected(($application->form_payment_status ?? 'pending') === 'paid')>Sudah Bayar</option>
                            </select>
                        </div>
                        <div>
                            <label for="form_payment_bank" class="text-sm font-semibold text-slate-700">Dibayar Melalui Bank</label>
                            <select id="form_payment_bank" name="form_payment_bank" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">Pilih Bank</option>
                                <option value="bca" @selected(($application->form_payment_bank ?? '') === 'bca')>BCA</option>
                                <option value="mandiri" @selected(($application->form_payment_bank ?? '') === 'mandiri')>Mandiri</option>
                                <option value="cimb" @selected(($application->form_payment_bank ?? '') === 'cimb')>CIMB</option>
                            </select>
                        </div>
                        <div>
                            <label for="form_payment_note" class="text-sm font-semibold text-slate-700">Catatan Pembayaran</label>
                            <textarea id="form_payment_note" name="form_payment_note" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('form_payment_note', $application->form_payment_note) }}</textarea>
                        </div>
                        <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                            Simpan Pembayaran
                        </button>
                    </form>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Dokumen</h3>
                    <div class="mt-5 space-y-3">
                        @forelse ($application->documents as $document)
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-sm font-bold text-slate-900">{{ strtoupper($document->type) }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $document->original_name }}</p>
                                <a href="{{ $document->url }}" target="_blank" class="mt-3 inline-flex rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-100">
                                    Lihat Dokumen
                                </a>
                            </div>
                        @empty
                            <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada dokumen yang diupload.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-950">Review Admin</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Ubah status setelah memeriksa data dan dokumen pendaftar.
                    </p>
                    <form method="POST" action="{{ route('admin.local-applications.status.update', $application) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="status" class="text-sm font-semibold text-slate-700">Status</label>
                            <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="submitted" @selected($application->status === 'submitted')>Menunggu Review</option>
                                <option value="verified" @selected($application->status === 'verified')>Terverifikasi</option>
                                <option value="rejected" @selected($application->status === 'rejected')>Ditolak/Revisi</option>
                            </select>
                        </div>
                        <div>
                            <label for="review_note" class="text-sm font-semibold text-slate-700">Catatan Review</label>
                            <textarea id="review_note" name="review_note" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('review_note', $application->review_note) }}</textarea>
                        </div>
                        <button type="submit" class="w-full rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                            Simpan Review
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
@endsection
