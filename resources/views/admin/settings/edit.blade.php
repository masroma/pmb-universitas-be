@extends('admin.layout')

@section('title', 'Setting Website')
@section('page_title', 'Setting Website')

@section('content')
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
        $fileClass = 'mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 outline-none transition file:mr-4 file:rounded-xl file:border-0 file:bg-white file:px-4 file:py-2 file:text-sm file:font-bold file:text-slate-700 hover:border-blue-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
        $labelClass = 'text-sm font-semibold text-slate-700';
    @endphp

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
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
                <p class="text-sm font-bold text-blue-700">Branding</p>
                <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Identitas Kampus</h2>
                <p class="max-w-2xl text-sm leading-6 text-slate-500">
                    Logo dan nama kampus dipakai di admin dan API setting untuk website PMB.
                </p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="campus_name" class="{{ $labelClass }}">Nama Kampus</label>
                    <input id="campus_name" name="campus_name" type="text" value="{{ old('campus_name', $campusSetting->campus_name) }}" required class="{{ $inputClass }}">
                    @error('campus_name')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pmb_tagline" class="{{ $labelClass }}">Tagline PMB</label>
                    <input id="pmb_tagline" name="pmb_tagline" type="text" value="{{ old('pmb_tagline', $campusSetting->pmb_tagline) }}" placeholder="Penerimaan Mahasiswa Baru 2026" class="{{ $inputClass }}">
                    <p class="mt-2 text-xs text-slate-500">Kosongkan untuk memakai tahun akademik periode PMB aktif.</p>
                    @error('pmb_tagline')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="hero_description" class="{{ $labelClass }}">Deskripsi Hero Landing Page</label>
                    <textarea id="hero_description" name="hero_description" rows="3" class="{{ $inputClass }}">{{ old('hero_description', $campusSetting->hero_description) }}</textarea>
                    @error('hero_description')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="website" class="{{ $labelClass }}">Website</label>
                    <input id="website" name="website" type="url" value="{{ old('website', $campusSetting->website) }}" placeholder="https://paramadina.ac.id" class="{{ $inputClass }}">
                    @error('website')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="logo_path" class="{{ $labelClass }}">Logo Kampus</label>
                    <input id="logo_path" name="logo_path" type="file" accept="image/*" class="{{ $fileClass }}">
                    @error('logo_path')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($campusSetting->logo_url)
                        <div class="mt-4 flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <img src="{{ $campusSetting->logo_url }}" alt="{{ $campusSetting->campus_name }}" class="h-14 w-14 rounded-xl object-contain ring-1 ring-slate-200">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Logo saat ini</p>
                                <p class="text-xs text-slate-500">Upload file baru untuk mengganti logo.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    <label for="hero_image_path" class="{{ $labelClass }}">Gambar Hero</label>
                    <input id="hero_image_path" name="hero_image_path" type="file" accept="image/*" class="{{ $fileClass }}">
                    @error('hero_image_path')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($campusSetting->hero_image_url)
                        <div class="mt-4 overflow-hidden rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                            <img src="{{ $campusSetting->hero_image_url }}" alt="Hero {{ $campusSetting->campus_name }}" class="h-40 w-full object-cover">
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-2">
                    <label for="address" class="{{ $labelClass }}">Alamat</label>
                    <textarea id="address" name="address" rows="4" class="{{ $inputClass }}">{{ old('address', $campusSetting->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-2 border-b border-slate-100 pb-6">
                <p class="text-sm font-bold text-blue-700">Kontak</p>
                <h2 class="text-2xl font-bold tracking-[-0.03em] text-slate-950">Kontak dan Sosial Media</h2>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div>
                    <label for="phone" class="{{ $labelClass }}">Telepon</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $campusSetting->phone) }}" class="{{ $inputClass }}">
                    @error('phone')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fax" class="{{ $labelClass }}">Fax</label>
                    <input id="fax" name="fax" type="text" value="{{ old('fax', $campusSetting->fax) }}" class="{{ $inputClass }}">
                    @error('fax')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="facebook" class="{{ $labelClass }}">Facebook</label>
                    <input id="facebook" name="facebook" type="url" value="{{ old('facebook', $campusSetting->facebook) }}" class="{{ $inputClass }}">
                    @error('facebook')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instagram" class="{{ $labelClass }}">Instagram</label>
                    <input id="instagram" name="instagram" type="url" value="{{ old('instagram', $campusSetting->instagram) }}" class="{{ $inputClass }}">
                    @error('instagram')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="twitter" class="{{ $labelClass }}">Twitter / X</label>
                    <input id="twitter" name="twitter" type="url" value="{{ old('twitter', $campusSetting->twitter) }}" class="{{ $inputClass }}">
                    @error('twitter')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="linkedin" class="{{ $labelClass }}">LinkedIn</label>
                    <input id="linkedin" name="linkedin" type="url" value="{{ old('linkedin', $campusSetting->linkedin) }}" class="{{ $inputClass }}">
                    @error('linkedin')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="youtube" class="{{ $labelClass }}">YouTube</label>
                    <input id="youtube" name="youtube" type="url" value="{{ old('youtube', $campusSetting->youtube) }}" class="{{ $inputClass }}">
                    @error('youtube')
                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="sticky bottom-4 flex justify-end">
            <button type="submit" class="rounded-2xl bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-blue-700/20 transition hover:bg-blue-800">
                Simpan Setting
            </button>
        </div>
    </form>
@endsection
