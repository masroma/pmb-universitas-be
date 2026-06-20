@extends('admin.layout')

@section('title', 'Dashboard Admin PMB')
@section('page_title', 'Dashboard')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div>
                <div>
                    <p class="text-sm font-semibold text-blue-700">Selamat datang</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-[-0.04em] text-slate-950">
                        Kelola PMB {{ $campusSetting->campus_name }}
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                        Admin custom ini dibuat ringan dan bersih untuk mengatur identitas kampus yang tampil di website PMB.
                    </p>
                </div>
                <a href="{{ route('admin.settings.edit') }}" class="mt-6 inline-flex w-fit items-center justify-center rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                    Edit Setting
                </a>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-bold text-slate-950">Identitas Kampus</p>
            <div class="mt-5 flex items-center gap-4">
                @if ($campusSetting->logo_url)
                    <img src="{{ $campusSetting->logo_url }}" alt="{{ $campusSetting->campus_name }}" class="h-16 w-16 rounded-2xl object-contain ring-1 ring-slate-200">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 font-bold text-blue-700 ring-1 ring-blue-100">
                        PMB
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-slate-950">{{ $campusSetting->campus_name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $campusSetting->website ?: 'Website belum diisi' }}</p>
                </div>
            </div>
            <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                {{ $campusSetting->address ?: 'Alamat kampus belum diisi.' }}
            </div>
        </section>
    </div>
@endsection
