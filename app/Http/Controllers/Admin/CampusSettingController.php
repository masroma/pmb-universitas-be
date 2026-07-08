<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CampusSettingController extends Controller
{
    public function edit(): View
    {
        $campusSetting = $this->setting();

        return view('admin.settings.edit', [
            'campusSetting' => $campusSetting,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $campusSetting = $this->setting();

        $validated = $request->validate([
            'campus_name' => ['required', 'string', 'max:255'],
            'pmb_tagline' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string'],
            'logo_path' => ['nullable', 'image', 'max:2048'],
            'hero_image_path' => ['nullable', 'image', 'max:4096'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['logo_path' => 'campus', 'hero_image_path' => 'campus/hero'] as $field => $directory) {
            if (! $request->hasFile($field)) {
                unset($validated[$field]);

                continue;
            }

            if ($campusSetting->{$field}) {
                Storage::disk('public')->delete($campusSetting->{$field});
            }

            $validated[$field] = $request->file($field)->store($directory, 'public');
        }

        $campusSetting->update($validated);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan kampus berhasil diperbarui.');
    }

    private function setting(): CampusSetting
    {
        return CampusSetting::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first() ?? CampusSetting::query()->create([
                'campus_name' => 'Universitas',
            ]);
    }
}
