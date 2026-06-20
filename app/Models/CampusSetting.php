<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CampusSetting extends Model
{
    protected $fillable = [
        'campus_name',
        'logo_path',
        'hero_image_path',
        'address',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'youtube',
        'fax',
        'phone',
    ];

    protected $appends = [
        'logo_url',
        'hero_image_url',
        'social_media',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        if (! $this->hero_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->hero_image_path);
    }

    /**
     * @return array<string, string>
     */
    public function getSocialMediaAttribute(): array
    {
        return collect([
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'youtube' => $this->youtube,
        ])->filter()->all();
    }
}
