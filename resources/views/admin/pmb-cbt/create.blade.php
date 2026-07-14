@extends('admin.layout')

@section('title', 'Tambah Soal CBT')
@section('page_title', 'Tambah Soal CBT')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-indigo-700">Bank Soal CBT</p>
                    <h2 class="mt-2 text-2xl font-bold tracking-[-0.03em] text-slate-950">Tambah Soal Baru</h2>
                    <p class="mt-2 text-sm text-slate-500">Isi pertanyaan, 4 opsi jawaban, dan kunci jawaban yang benar.</p>
                </div>
                <a href="{{ route('admin.pmb-cbt.index') }}" class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Kembali
                </a>
            </div>

            <form method="POST" action="{{ route('admin.pmb-cbt.store') }}" class="mt-8 space-y-6">
                @csrf
                @include('admin.pmb-cbt._form')
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="rounded-2xl bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800">
                        Simpan Soal
                    </button>
                    <a href="{{ route('admin.pmb-cbt.index') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </a>
                </div>
            </form>
        </section>
    </div>
@endsection
