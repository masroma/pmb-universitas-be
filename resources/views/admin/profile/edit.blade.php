@extends('admin.layout')

@section('title', 'Edit Profil')
@section('page_title', 'Edit Profil')

@section('content')
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
        $labelClass = 'text-sm font-semibold text-slate-700';
    @endphp

    <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6">
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
                <p class="text-sm font-bold text-blue-700">Akun</p>
                <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Informasi Profil</h2>
                <p class="max-w-2xl text-sm leading-6 text-slate-500">
                    Perbarui nama, email, dan nomor telepon akun admin Anda.
                </p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="name" class="{{ $labelClass }}">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="{{ $inputClass }}">
                    @error('name')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="{{ $labelClass }}">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="{{ $inputClass }}">
                    @error('email')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="{{ $labelClass }}">Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx" class="{{ $inputClass }}">
                    @error('phone')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </section>
    </form>
@endsection
