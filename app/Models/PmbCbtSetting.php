<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbCbtSetting extends Model
{
    protected $fillable = [
        'title',
        'duration_minutes',
        'questions_per_attempt',
        'pass_score',
        'max_attempts',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'title' => 'Tes Seleksi PMB',
            'duration_minutes' => 30,
            'questions_per_attempt' => 10,
            'pass_score' => 60,
            'max_attempts' => 3,
            'instructions' => "1. Pastikan koneksi internet stabil.\n2. Kerjakan soal sesuai waktu yang tersedia.\n3. Nilai minimal kelulusan sesuai ketentuan.\n4. Setelah lulus, Anda dapat melanjutkan pengisian biodata dan upload dokumen.",
            'is_active' => true,
        ]);
    }
}
