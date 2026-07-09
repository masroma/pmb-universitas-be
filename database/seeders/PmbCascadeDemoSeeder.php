<?php

namespace Database\Seeders;

use App\Models\PmbOpenStudyProgram;
use App\Models\PmbSyncedRegistrationPeriod;
use Illuminate\Database\Seeder;

class PmbCascadeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateString();
        $periodId = '305';

        PmbSyncedRegistrationPeriod::query()->updateOrCreate(
            ['sevima_id' => $periodId],
            [
                'nama_periode_pendaftaran' => 'Gelombang 1 2025/2026',
                'tanggal_awal_pendaftaran' => $now,
                'tanggal_akhir_pendaftaran' => now()->addMonths(6)->toDateString(),
                'status_periode_pendaftaran' => 'Aktif',
                'id_status_periode_pendaftaran' => 'A',
                'keterangan' => 'Periode pendaftaran demo untuk uji cascade Step 1.',
                'is_active' => true,
                'synced_at' => now(),
            ],
        );

        $programs = [
            ['jenjang' => 'S1', 'id' => 101, 'name' => 'Manajemen', 'lokasi' => 'Cipayung'],
            ['jenjang' => 'S1', 'id' => 102, 'name' => 'Ilmu Komunikasi', 'lokasi' => 'Kuningan'],
            ['jenjang' => 'S1', 'id' => 103, 'name' => 'Psikologi', 'lokasi' => 'Cikarang'],
            ['jenjang' => 'S2', 'id' => 201, 'name' => 'Manajemen', 'lokasi' => 'Cipayung'],
            ['jenjang' => 'S2', 'id' => 202, 'name' => 'Ilmu Komunikasi', 'lokasi' => 'Kuningan'],
        ];

        $s1Classes = [
            'S1 Kelas A (09.45 - 18.00 WIB)',
            'S1 Kelas B (18.30 - 21.00 WIB) + Online (Sabtu)',
            'S1 Kelas C (Sabtu Sesi 1) + Online (On Weekdays)',
            'S1 Kelas D (Sabtu sesi 2) + Online (On Weekdays)',
        ];

        $s1Jalur = [
            ['id' => 23, 'name' => 'Jalur SMA/SMK'],
            ['id' => 10, 'name' => 'Jalur Tes Potensial Akademik'],
            ['id' => 3, 'name' => 'Pindahan'],
            ['id' => 29, 'name' => 'RPL Transfer SKS'],
            ['id' => 30, 'name' => 'RPL Perolehan SKS'],
            ['id' => 17, 'name' => 'Beasiswa Paramadina Fellowship'],
            ['id' => 40, 'name' => 'Beasiswa Gojek-Paramadina'],
        ];

        $counter = 1;

        foreach ($programs as $program) {
            if ($program['jenjang'] === 'S1') {
                foreach ($s1Classes as $className) {
                    foreach ($s1Jalur as $jalur) {
                        if (in_array($jalur['name'], ['Pindahan', 'RPL Transfer SKS', 'RPL Perolehan SKS'], true)
                            && $className !== 'S1 Kelas A (09.45 - 18.00 WIB)') {
                            continue;
                        }

                        $this->seedOpenProgram(
                            $counter++,
                            $program,
                            $className,
                            $jalur['id'],
                            $jalur['name'],
                            $periodId,
                        );
                    }
                }

                continue;
            }

            $this->seedOpenProgram(
                $counter++,
                $program,
                'S2 Kelas Weekend',
                10,
                'Jalur Tes Potensial Akademik',
                $periodId,
            );
            $this->seedOpenProgram(
                $counter++,
                $program,
                'S2 Kelas Weekday',
                12,
                'Seleksi Mandiri',
                $periodId,
            );
        }

        $this->command?->info('PmbCascadeDemoSeeder: data cascade demo tersimpan.');
    }

  /**
   * @param  array{jenjang: string, id: int, name: string, lokasi: string}  $program
   */
    private function seedOpenProgram(
        int $counter,
        array $program,
        string $className,
        int $jalurId,
        string $jalurName,
        string $periodId,
    ): void {
        $sistemKuliah = $program['jenjang'].' '.$program['name'].' ('.$program['lokasi'].')';

        PmbOpenStudyProgram::query()->updateOrCreate(
            ['sevima_id' => 'demo-'.$counter],
            [
                'jenjang_program_studi' => $program['jenjang'],
                'id_program_studi' => $program['id'],
                'program_studi' => $program['name'],
                'sistem_kuliah' => $sistemKuliah,
                'lokasi' => $program['lokasi'],
                'id_periode_pendaftaran' => $periodId,
                'nama_periode_pendaftaran' => $className,
                'id_jalur_pendaftaran' => $jalurId,
                'jalur_pendaftaran' => $jalurName,
                'id_gelombang' => 1,
                'gelombang' => 'Gelombang 1',
                'registration_fee' => $program['jenjang'] === 'S2' ? 500000 : 300000,
                'is_active' => true,
                'synced_at' => now(),
            ],
        );
    }
}
