@extends('admin.layout')

@section('title', 'Pembayaran DOKU')
@section('page_title', 'Pembayaran DOKU')

@section('content')
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
        $labelClass = 'text-sm font-semibold text-slate-700';
    @endphp

    <form method="POST" action="{{ route('admin.payment-gateway.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

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
                <p class="text-sm font-bold text-blue-700">Payment Gateway</p>
                <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Integrasi DOKU Checkout</h2>
                <p class="max-w-2xl text-sm leading-6 text-slate-500">
                    Isi Client ID dan Secret Key dari dashboard DOKU. Setelah aktif, mahasiswa dapat membayar biaya formulir melalui halaman checkout DOKU.
                </p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <input
                            type="checkbox"
                            name="is_enabled"
                            value="1"
                            class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500"
                            @checked(old('is_enabled', $setting->is_enabled))
                        >
                        <span class="text-sm font-semibold text-slate-800">Aktifkan pembayaran DOKU</span>
                    </label>
                    <p class="mt-2 text-xs text-slate-500">
                        Status:
                        @if ($setting->isConfigured())
                            <span class="font-bold text-emerald-700">Siap digunakan</span>
                        @else
                            <span class="font-bold text-amber-700">Belum lengkap / nonaktif</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label for="environment" class="{{ $labelClass }}">Environment</label>
                    <select id="environment" name="environment" class="{{ $inputClass }}" required>
                        <option value="sandbox" @selected(old('environment', $setting->environment) === 'sandbox')>Sandbox (Testing)</option>
                        <option value="production" @selected(old('environment', $setting->environment) === 'production')>Production (Live)</option>
                    </select>
                </div>

                <div>
                    <label for="client_id" class="{{ $labelClass }}">Client ID</label>
                    <input
                        id="client_id"
                        name="client_id"
                        type="text"
                        value="{{ old('client_id', $setting->client_id) }}"
                        placeholder="Client ID dari dashboard DOKU"
                        class="{{ $inputClass }}"
                    >
                </div>

                <div class="lg:col-span-2">
                    <label for="secret_key" class="{{ $labelClass }}">Secret Key</label>
                    <input
                        id="secret_key"
                        name="secret_key"
                        type="password"
                        value=""
                        placeholder="{{ $setting->maskedSecretKey() ? 'Kosongkan jika tidak ingin mengubah (tersimpan: '.$setting->maskedSecretKey().')' : 'Secret Key dari dashboard DOKU' }}"
                        class="{{ $inputClass }}"
                        autocomplete="new-password"
                    >
                    <p class="mt-2 text-xs text-slate-500">Secret Key disimpan terenkripsi di database. Kosongkan field ini jika tidak ingin mengganti.</p>
                </div>

                <div class="lg:col-span-2">
                    <label for="callback_url" class="{{ $labelClass }}">Callback URL (opsional)</label>
                    <input
                        id="callback_url"
                        name="callback_url"
                        type="url"
                        value="{{ old('callback_url', $setting->callback_url) }}"
                        placeholder="https://pmb-universitas-fe.vercel.app/portal-mahasiswa?view=pembayaran"
                        class="{{ $inputClass }}"
                    >
                    <p class="mt-2 text-xs text-slate-500">
                        URL kembali setelah mahasiswa selesai di halaman DOKU. Kosongkan untuk memakai FRONTEND_URL + `/portal-mahasiswa?view=pembayaran`.
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h3 class="text-lg font-bold text-slate-950">Notification URL (Webhook)</h3>
            <p class="mt-2 text-sm text-slate-500">
                Salin URL ini ke pengaturan Notification di dashboard DOKU agar status pembayaran otomatis masuk ke sistem.
            </p>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <code class="flex-1 break-all rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800">{{ $notificationUrl }}</code>
                <button
                    type="button"
                    class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white"
                    onclick="navigator.clipboard.writeText(@js($notificationUrl))"
                >
                    Copy
                </button>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="rounded-2xl bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                Simpan Pengaturan DOKU
            </button>
        </div>
    </form>
@endsection
