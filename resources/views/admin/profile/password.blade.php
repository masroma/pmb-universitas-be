@extends('admin.layout')

@section('title', 'Edit Password')
@section('page_title', 'Edit Password')

@section('content')
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
        $labelClass = 'text-sm font-semibold text-slate-700';
    @endphp

    <form method="POST" action="{{ route('admin.profile.password.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

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
                <p class="text-sm font-bold text-blue-700">Keamanan</p>
                <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Ubah Password</h2>
                <p class="max-w-2xl text-sm leading-6 text-slate-500">
                    Gunakan password minimal 8 karakter untuk menjaga keamanan akun admin.
                </p>
            </div>

            <div class="mt-6 max-w-xl space-y-6">
                <div>
                    <label for="current_password" class="{{ $labelClass }}">Password Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" required class="{{ $inputClass }}">
                    @error('current_password')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="{{ $labelClass }}">Password Baru</label>
                    <input id="password" name="password" type="password" required class="{{ $inputClass }}">
                    @error('password')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="{{ $inputClass }}">
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    Simpan Password
                </button>
            </div>
        </section>
    </form>
@endsection
