<?php

namespace App\Support;

use App\Models\CampusSetting;
use Illuminate\Support\Facades\DB;

class CampusBranding
{
    public static function setting(): CampusSetting
    {
        return CampusSetting::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first() ?? CampusSetting::query()->create([
                'campus_name' => config('app.name'),
            ]);
    }

    public static function resolvePmbTagline(?CampusSetting $setting = null): string
    {
        $setting ??= self::setting();

        if (filled($setting->pmb_tagline)) {
            return $setting->pmb_tagline;
        }

        $academicYear = DB::table('pmb_admission_periods')
            ->where('is_active', true)
            ->orderByDesc('starts_at')
            ->value('academic_year');

        return $academicYear
            ? 'Penerimaan Mahasiswa Baru '.$academicYear
            : 'Penerimaan Mahasiswa Baru';
    }

    public static function resolveHeroDescription(?CampusSetting $setting = null): string
    {
        $setting ??= self::setting();

        if (filled($setting->hero_description)) {
            return $setting->hero_description;
        }

        return 'Bergabunglah bersama kami untuk memulai perjalanan pendidikan tinggi Anda.';
    }

    /**
     * @return array<string, mixed>
     */
    public static function apiPayload(?CampusSetting $setting = null): array
    {
        $setting ??= self::setting();

        return [
            ...$setting->toArray(),
            'pmb_tagline' => self::resolvePmbTagline($setting),
            'hero_description' => self::resolveHeroDescription($setting),
        ];
    }
}
