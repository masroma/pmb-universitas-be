@extends('admin.layout')

@section('title', 'Edit Konten PMB')
@section('page_title', 'Edit Konten PMB')

@section('content')
    <form method="POST" action="{{ route('admin.pmb-information.update', $section) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="filter_program_level" value="{{ request('program_level') }}">
        <input type="hidden" name="filter_category" value="{{ request('category') }}">
        <input type="hidden" name="page" value="{{ request('page') }}">

        @include('admin.pmb-information._form')

        <div class="sticky bottom-4 flex justify-end gap-3">
            <a href="{{ route('admin.pmb-information.index', request()->only(['q', 'program_level', 'category', 'page'])) }}" class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Batal
            </a>
            <button type="submit" class="rounded-2xl bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-blue-700/20 transition hover:bg-blue-800">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
