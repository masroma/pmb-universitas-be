<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AdmissionPathSeeder extends Seeder
{
    /**
     * Import daftar jalur pendaftaran (sumber: API SEVIMA /v1/jalur-pendaftaran)
     * ke tabel lokal admission_paths agar tidak lagi bergantung pada API vendor.
     */
    public function run(): void
    {
        $institutionId = (int) DB::table('institutions')->where('code', 'paramadina')->value('id');

        if ($institutionId === 0) {
            $institutionId = (int) DB::table('institutions')->orderBy('id')->value('id');
        }

        if ($institutionId === 0) {
            $this->command?->warn('AdmissionPathSeeder dilewati: belum ada institusi. Jalankan StandalonePmbSeeder terlebih dahulu.');

            return;
        }

        $now = now();
        $paths = $this->paths();

        foreach ($paths as $index => $path) {
            $sevimaId = (int) $path['id_jalur_pendaftaran'];
            $description = trim((string) ($path['keterangan_jalur_pendaftaran'] ?? ''));

            DB::table('admission_paths')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => 'sevima-'.$sevimaId],
                [
                    'sevima_id' => $sevimaId,
                    'name' => Str::squish((string) $path['nama_jalur_pendaftaran']),
                    'description' => $description !== '' ? $description : null,
                    'jenis_pendaftaran_id' => $path['id_jenis_pendaftaran'] ?? null,
                    'jenis_pendaftaran_name' => $path['nama_jenis_pendaftaran'] ?? null,
                    'registration_fee' => 0,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        $this->command?->info('AdmissionPathSeeder: '.count($paths).' jalur pendaftaran tersimpan ke db lokal.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function paths(): array
    {
        $file = database_path('data/jalur-pendaftaran.json');

        if (! is_file($file)) {
            throw new RuntimeException("Berkas data jalur pendaftaran tidak ditemukan: {$file}");
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Berkas data jalur pendaftaran tidak valid: {$file}");
        }

        return $decoded;
    }
}
