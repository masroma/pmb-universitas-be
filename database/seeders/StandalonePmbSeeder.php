<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StandalonePmbSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        $institutionId = $this->seedInstitution();
        $campusIds = $this->seedCampuses($institutionId);
        $facultyIds = $this->seedFaculties($institutionId);
        $programIds = $this->seedStudyPrograms($institutionId, $facultyIds);
        $campusProgramIds = $this->seedCampusPrograms($campusIds, $programIds);
        $classTypeIds = $this->seedClassTypes($institutionId);
        $periodId = $this->seedAdmissionPeriod($institutionId);
        $waveIds = $this->seedWaves($periodId);
        $pathIds = $this->seedAdmissionPaths($institutionId);

        $this->seedRegistrationOptions(
            $periodId,
            $waveIds,
            $campusProgramIds,
            $pathIds,
            $classTypeIds,
        );
        $this->seedScholarships($institutionId);
        $this->seedContent($institutionId, $campusIds);
        $this->seedFaqs($institutionId, $campusIds);
    }

    private function seedInstitution(): int
    {
        DB::table('institutions')->updateOrInsert(
            ['code' => 'paramadina'],
            [
                'name' => 'Universitas Paramadina',
                'short_name' => 'Paramadina',
                'website' => 'https://paramadina.ac.id',
                'description' => 'Universitas berbasis nilai keislaman, kebangsaan, dan kewirausahaan dengan pilihan program lintas jenjang.',
                'is_active' => true,
                'updated_at' => $this->now,
                'created_at' => $this->now,
            ],
        );

        return (int) DB::table('institutions')->where('code', 'paramadina')->value('id');
    }

    /**
     * @return array<string, int>
     */
    private function seedCampuses(int $institutionId): array
    {
        $campuses = [
            [
                'code' => 'cipayung',
                'name' => 'Kampus Cipayung',
                'city' => 'Jakarta Timur',
                'province' => 'DKI Jakarta',
                'address' => 'Jl. Raya Mabes Hankam Kav. 9, Cipayung, Jakarta Timur',
                'is_main' => true,
            ],
            [
                'code' => 'kuningan',
                'name' => 'Kampus Kuningan',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'address' => 'Jl. Gatot Subroto Kav. 97, Jakarta Selatan',
                'is_main' => false,
            ],
            [
                'code' => 'cikarang',
                'name' => 'Kampus Cikarang',
                'city' => 'Bekasi',
                'province' => 'Jawa Barat',
                'address' => 'Kawasan Cikarang, Bekasi',
                'is_main' => false,
            ],
        ];

        foreach ($campuses as $index => $campus) {
            DB::table('campuses')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => $campus['code']],
                [
                    ...$campus,
                    'institution_id' => $institutionId,
                    'maps_url' => null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        $campusIds = DB::table('campuses')
            ->where('institution_id', $institutionId)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $contacts = [
            ['campus' => 'kuningan', 'type' => 'whatsapp', 'label' => 'WhatsApp PMB', 'value' => '0812-0000-0000', 'is_primary' => true],
            ['campus' => 'kuningan', 'type' => 'email', 'label' => 'Email PMB', 'value' => 'admission@paramadina.ac.id', 'is_primary' => true],
            ['campus' => 'cipayung', 'type' => 'whatsapp', 'label' => 'WhatsApp Cipayung', 'value' => '0812-1111-1111', 'is_primary' => false],
            ['campus' => 'cikarang', 'type' => 'whatsapp', 'label' => 'WhatsApp Cikarang', 'value' => '0812-2222-2222', 'is_primary' => false],
        ];

        foreach ($contacts as $index => $contact) {
            DB::table('campus_contacts')->updateOrInsert(
                [
                    'campus_id' => $campusIds[$contact['campus']],
                    'type' => $contact['type'],
                    'value' => $contact['value'],
                ],
                [
                    'label' => $contact['label'],
                    'is_primary' => $contact['is_primary'],
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        return $campusIds;
    }

    /**
     * @return array<string, int>
     */
    private function seedFaculties(int $institutionId): array
    {
        $faculties = [
            ['code' => 'feb', 'name' => 'Fakultas Ekonomi dan Bisnis'],
            ['code' => 'falsafah', 'name' => 'Fakultas Falsafah dan Peradaban'],
            ['code' => 'fikom', 'name' => 'Fakultas Ilmu Komunikasi'],
            ['code' => 'fpsi', 'name' => 'Fakultas Psikologi'],
            ['code' => 'fst', 'name' => 'Fakultas Sains dan Teknologi'],
            ['code' => 'pascasarjana', 'name' => 'Sekolah Pascasarjana'],
        ];

        foreach ($faculties as $faculty) {
            DB::table('faculties')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => $faculty['code']],
                [
                    ...$faculty,
                    'institution_id' => $institutionId,
                    'is_active' => true,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        return DB::table('faculties')
            ->where('institution_id', $institutionId)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param array<string, int> $facultyIds
     * @return array<string, int>
     */
    private function seedStudyPrograms(int $institutionId, array $facultyIds): array
    {
        $programs = [
            ['code' => 's1-manajemen', 'faculty' => 'feb', 'level' => 'S1', 'name' => 'Manajemen', 'degree' => 'S.M.', 'accreditation' => 'Unggul'],
            ['code' => 's1-hi', 'faculty' => 'falsafah', 'level' => 'S1', 'name' => 'Ilmu Hubungan Internasional', 'degree' => 'S.Hub.Int.', 'accreditation' => 'B'],
            ['code' => 's1-komunikasi', 'faculty' => 'fikom', 'level' => 'S1', 'name' => 'Ilmu Komunikasi', 'degree' => 'S.I.Kom.', 'accreditation' => 'Baik Sekali'],
            ['code' => 's1-psikologi', 'faculty' => 'fpsi', 'level' => 'S1', 'name' => 'Psikologi', 'degree' => 'S.Psi.', 'accreditation' => 'Baik Sekali'],
            ['code' => 's1-falsafah-agama', 'faculty' => 'falsafah', 'level' => 'S1', 'name' => 'Falsafah dan Agama', 'degree' => 'S.Ag.', 'accreditation' => 'Unggul'],
            ['code' => 's1-informatika', 'faculty' => 'fst', 'level' => 'S1', 'name' => 'Teknik Informatika', 'degree' => 'S.Kom.', 'accreditation' => 'Baik Sekali'],
            ['code' => 's1-desain-produk', 'faculty' => 'fst', 'level' => 'S1', 'name' => 'Desain Produk', 'degree' => 'S.Ds.', 'accreditation' => 'Baik'],
            ['code' => 's1-dkv', 'faculty' => 'fst', 'level' => 'S1', 'name' => 'Desain Komunikasi Visual', 'degree' => 'S.Ds.', 'accreditation' => 'A'],
            ['code' => 's2-manajemen', 'faculty' => 'pascasarjana', 'level' => 'S2', 'name' => 'Manajemen', 'degree' => 'M.M.', 'accreditation' => 'B'],
            ['code' => 's2-komunikasi', 'faculty' => 'pascasarjana', 'level' => 'S2', 'name' => 'Ilmu Komunikasi', 'degree' => 'M.I.Kom.', 'accreditation' => 'Baik Sekali'],
            ['code' => 's2-psikologi', 'faculty' => 'pascasarjana', 'level' => 'S2', 'name' => 'Psikologi', 'degree' => 'M.Psi.', 'accreditation' => null],
            ['code' => 's3-manajemen', 'faculty' => 'pascasarjana', 'level' => 'S3', 'name' => 'Ilmu Manajemen', 'degree' => 'Dr.', 'accreditation' => null],
        ];

        foreach ($programs as $index => $program) {
            DB::table('study_programs')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => $program['code']],
                [
                    'institution_id' => $institutionId,
                    'faculty_id' => $facultyIds[$program['faculty']] ?? null,
                    'code' => $program['code'],
                    'level' => $program['level'],
                    'name' => $program['name'],
                    'degree' => $program['degree'],
                    'accreditation' => $program['accreditation'],
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        return DB::table('study_programs')
            ->where('institution_id', $institutionId)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param array<string, int> $campusIds
     * @param array<string, int> $programIds
     * @return array<string, int>
     */
    private function seedCampusPrograms(array $campusIds, array $programIds): array
    {
        $map = [
            'cipayung' => ['s1-manajemen', 's1-hi', 's1-komunikasi', 's1-psikologi', 's1-falsafah-agama', 's1-informatika', 's1-desain-produk', 's1-dkv', 's2-manajemen', 's2-komunikasi', 's2-psikologi', 's3-manajemen'],
            'cikarang' => ['s1-manajemen', 's1-komunikasi', 's1-informatika', 's1-psikologi', 's2-manajemen', 's2-komunikasi', 's3-manajemen'],
        ];

        foreach ($map as $campusCode => $programCodes) {
            foreach ($programCodes as $index => $programCode) {
                DB::table('campus_study_programs')->updateOrInsert(
                    [
                        'campus_id' => $campusIds[$campusCode],
                        'study_program_id' => $programIds[$programCode],
                    ],
                    [
                        'is_open' => true,
                        'sort_order' => $index + 1,
                        'updated_at' => $this->now,
                        'created_at' => $this->now,
                    ],
                );
            }
        }

        return DB::table('campus_study_programs')
            ->join('campuses', 'campuses.id', '=', 'campus_study_programs.campus_id')
            ->join('study_programs', 'study_programs.id', '=', 'campus_study_programs.study_program_id')
            ->select('campus_study_programs.id', 'campuses.code as campus_code', 'study_programs.code as program_code')
            ->get()
            ->mapWithKeys(fn ($row): array => [$row->campus_code.'|'.$row->program_code => (int) $row->id])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function seedClassTypes(int $institutionId): array
    {
        $classes = [
            ['code' => 'reguler-pagi', 'name' => 'Reguler Pagi', 'schedule_label' => 'Senin-Jumat pagi/siang', 'description' => 'Kelas reguler untuk mahasiswa penuh waktu.'],
            ['code' => 'malam', 'name' => 'Kelas Malam', 'schedule_label' => 'Hari kerja malam', 'description' => 'Cocok untuk calon mahasiswa yang bekerja.'],
            ['code' => 'sabtu', 'name' => 'Kelas Sabtu', 'schedule_label' => 'Sabtu pagi/siang', 'description' => 'Cocok untuk mahasiswa dengan jadwal kerja reguler.'],
            ['code' => 'hybrid', 'name' => 'Hybrid / Mix Learning', 'schedule_label' => 'Kombinasi online dan offline', 'description' => 'Perkuliahan memadukan sesi daring dan tatap muka.', 'is_online' => true],
        ];

        foreach ($classes as $index => $class) {
            DB::table('class_types')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => $class['code']],
                [
                    ...$class,
                    'institution_id' => $institutionId,
                    'is_online' => $class['is_online'] ?? false,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        return DB::table('class_types')
            ->where('institution_id', $institutionId)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function seedAdmissionPeriod(int $institutionId): int
    {
        DB::table('pmb_admission_periods')->updateOrInsert(
            ['institution_id' => $institutionId, 'code' => '2026-2027'],
            [
                'name' => 'PMB Tahun Akademik 2026/2027',
                'academic_year' => '2026/2027',
                'starts_at' => '2025-11-01',
                'ends_at' => '2026-09-27',
                'brochure_url' => 'https://admission.paramadina.ac.id/brosur',
                'is_active' => true,
                'updated_at' => $this->now,
                'created_at' => $this->now,
            ],
        );

        return (int) DB::table('pmb_admission_periods')
            ->where('institution_id', $institutionId)
            ->where('code', '2026-2027')
            ->value('id');
    }

    /**
     * @return array<string, int>
     */
    private function seedWaves(int $periodId): array
    {
        $waves = [
            ['code' => 'gel-1', 'name' => 'Gelombang 1', 'starts_at' => '2025-11-01', 'ends_at' => '2026-03-31'],
            ['code' => 'gel-2', 'name' => 'Gelombang 2', 'starts_at' => '2026-04-01', 'ends_at' => '2026-08-28'],
            ['code' => 'gel-3', 'name' => 'Gelombang 3', 'starts_at' => '2026-08-29', 'ends_at' => '2026-09-27'],
        ];

        foreach ($waves as $index => $wave) {
            DB::table('pmb_waves')->updateOrInsert(
                ['admission_period_id' => $periodId, 'code' => $wave['code']],
                [
                    ...$wave,
                    'admission_period_id' => $periodId,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        return DB::table('pmb_waves')
            ->where('admission_period_id', $periodId)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function seedAdmissionPaths(int $institutionId): array
    {
        $paths = [
            ['code' => 'reguler', 'name' => 'Reguler (Kelas A)', 'description' => 'Kelas A untuk mahasiswa penuh waktu dengan jadwal kuliah reguler.', 'registration_fee' => 300000],
            ['code' => 'karyawan', 'name' => 'Kelas B & C', 'description' => 'Pilihan kelas B dan C dengan jadwal kuliah yang lebih fleksibel.', 'registration_fee' => 350000],
            ['code' => 'rpl', 'name' => 'RPL', 'description' => 'Rekognisi Pembelajaran Lampau untuk penyetaraan pengalaman belajar dan kerja sebelumnya.', 'registration_fee' => 500000],
        ];

        foreach ($paths as $index => $path) {
            DB::table('admission_paths')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => $path['code']],
                [
                    ...$path,
                    'institution_id' => $institutionId,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        return DB::table('admission_paths')
            ->where('institution_id', $institutionId)
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param array<string, int> $waveIds
     * @param array<string, int> $campusProgramIds
     * @param array<string, int> $pathIds
     * @param array<string, int> $classTypeIds
     */
    private function seedRegistrationOptions(
        int $periodId,
        array $waveIds,
        array $campusProgramIds,
        array $pathIds,
        array $classTypeIds,
    ): void {
        // Prodi otomatis mengikuti jenjang; biaya semester mengikuti jenjang program studi.
        $semesterFeeByLevel = ['S1' => 8700000, 'S2' => 12000000, 'S3' => 18000000];
        // Jalur masuk beserta pilihan kelas dan biaya pendaftaran per jalur.
        $classesByPath = [
            'reguler' => ['reguler-pagi', 'malam'],
            'karyawan' => ['malam', 'sabtu'],
            'rpl' => ['sabtu', 'hybrid'],
        ];
        $registrationFeeByPath = ['reguler' => 300000, 'karyawan' => 350000, 'rpl' => 500000];

        foreach (array_keys($campusProgramIds) as $campusProgramKey) {
            [, $programCode] = explode('|', $campusProgramKey);
            $level = strtoupper(explode('-', $programCode)[0]);
            $semesterFee = $semesterFeeByLevel[$level] ?? 8700000;
            $installmentAmount = (int) round($semesterFee / 6);

            foreach ($classesByPath as $pathCode => $classCodes) {
                foreach ($classCodes as $classCode) {
                foreach (['gel-1', 'gel-2'] as $waveCode) {
                    DB::table('pmb_registration_options')->updateOrInsert(
                        [
                            'admission_period_id' => $periodId,
                            'wave_id' => $waveIds[$waveCode],
                            'campus_study_program_id' => $campusProgramIds[$campusProgramKey],
                            'admission_path_id' => $pathIds[$pathCode],
                            'class_type_id' => $classTypeIds[$classCode],
                        ],
                        [
                            'is_active' => true,
                            'updated_at' => $this->now,
                            'created_at' => $this->now,
                        ],
                    );

                    $registrationOptionId = (int) DB::table('pmb_registration_options')
                        ->where('admission_period_id', $periodId)
                        ->where('wave_id', $waveIds[$waveCode])
                        ->where('campus_study_program_id', $campusProgramIds[$campusProgramKey])
                        ->where('admission_path_id', $pathIds[$pathCode])
                        ->where('class_type_id', $classTypeIds[$classCode])
                        ->value('id');

                    DB::table('tuition_fee_schemes')->updateOrInsert(
                        ['registration_option_id' => $registrationOptionId],
                        [
                            'registration_fee' => $registrationFeeByPath[$pathCode],
                            'installment_count' => 6,
                            'installment_amount' => $installmentAmount,
                            'semester_fee' => $semesterFee,
                            'total_first_payment' => $installmentAmount,
                            'currency' => 'IDR',
                            'notes' => 'Estimasi biaya tenant Paramadina, dapat disesuaikan per kampus dan jalur.',
                            'is_active' => true,
                            'updated_at' => $this->now,
                            'created_at' => $this->now,
                        ],
                    );
                    }
                }
            }
        }
    }

    private function seedScholarships(int $institutionId): void
    {
        $scholarships = [
            ['code' => 'beasiswa-prestasi', 'name' => 'Beasiswa Prestasi', 'description' => 'Potongan biaya untuk calon mahasiswa berprestasi.', 'requirements' => ['Rapor/ijazah', 'Sertifikat prestasi', 'Mengikuti seleksi administrasi']],
            ['code' => 'beasiswa-guru', 'name' => 'Beasiswa Guru', 'description' => 'Program beasiswa untuk guru dan tenaga pendidik.', 'requirements' => ['Surat keterangan bekerja', 'KTP', 'Ijazah terakhir']],
            ['code' => 'beasiswa-kerjasama', 'name' => 'Beasiswa Kerja Sama', 'description' => 'Beasiswa untuk mitra perusahaan, komunitas, atau pemerintah.', 'requirements' => ['Surat rekomendasi mitra', 'Dokumen identitas', 'Mengikuti ketentuan kerja sama']],
        ];

        foreach ($scholarships as $index => $scholarship) {
            DB::table('scholarships')->updateOrInsert(
                ['institution_id' => $institutionId, 'code' => $scholarship['code']],
                [
                    ...$scholarship,
                    'institution_id' => $institutionId,
                    'requirements' => json_encode($scholarship['requirements']),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }
    }

    /**
     * @param array<string, int> $campusIds
     */
    private function seedContent(int $institutionId, array $campusIds): void
    {
        $contents = [
            ['category' => 'keunggulan', 'title' => 'Kampus fleksibel untuk mahasiswa aktif dan pekerja', 'body' => 'Tersedia pilihan kelas reguler, malam, Sabtu, dan hybrid sesuai kebijakan program studi.', 'items' => ['Pilihan waktu kuliah fleksibel', 'Dosen akademisi dan praktisi', 'Tidak ada batasan usia pendaftar']],
            ['category' => 'syarat', 'title' => 'Syarat umum pendaftaran', 'body' => 'Calon mahasiswa menyiapkan identitas diri dan dokumen pendidikan terakhir.', 'items' => ['KTP atau identitas resmi', 'Ijazah atau surat keterangan lulus', 'Transkrip nilai untuk pascasarjana', 'Pas foto terbaru']],
            ['category' => 'alur-pendaftaran', 'title' => 'Alur pendaftaran mahasiswa baru', 'body' => 'Pendaftar membuat akun, memilih prodi, melengkapi data, mengikuti seleksi, lalu melakukan daftar ulang.', 'items' => ['Buat akun pendaftaran', 'Pilih jalur, kampus, prodi, dan kelas', 'Bayar biaya pendaftaran jika berlaku', 'Lengkapi berkas', 'Ikuti seleksi', 'Daftar ulang setelah lulus']],
            ['category' => 'kurikulum', 'title' => 'Kurikulum berbasis kebutuhan industri', 'body' => 'Kurikulum dapat dikonfigurasi per program studi oleh masing-masing kampus.', 'items' => ['Mata kuliah inti', 'Mata kuliah pilihan', 'Proyek/praktikum', 'Tugas akhir atau tesis/disertasi']],
            ['category' => 'kontak', 'title' => 'Kontak PMB', 'body' => 'Tim PMB siap membantu calon mahasiswa memilih jalur, program studi, kelas, dan estimasi biaya.', 'items' => ['WhatsApp PMB', 'Email admission', 'Website pendaftaran']],
        ];

        foreach ($contents as $index => $content) {
            DB::table('pmb_content_blocks')->updateOrInsert(
                ['institution_id' => $institutionId, 'category' => $content['category'], 'title' => $content['title']],
                [
                    ...$content,
                    'institution_id' => $institutionId,
                    'campus_id' => null,
                    'study_program_id' => null,
                    'items' => json_encode($content['items']),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }

        foreach ($campusIds as $campusCode => $campusId) {
            DB::table('pmb_content_blocks')->updateOrInsert(
                ['institution_id' => $institutionId, 'campus_id' => $campusId, 'category' => 'lokasi-kampus'],
                [
                    'title' => 'Informasi '.str_replace('-', ' ', $campusCode),
                    'subtitle' => null,
                    'body' => 'Cabang kampus aktif yang dapat dipakai untuk konfigurasi PMB multi-lokasi.',
                    'items' => json_encode(['Alamat dan kontak dapat disesuaikan dari master kampus.']),
                    'is_active' => true,
                    'sort_order' => 10,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }
    }

    /**
     * @param array<string, int> $campusIds
     */
    private function seedFaqs(int $institutionId, array $campusIds): void
    {
        $faqs = [
            ['category' => 'pendaftaran', 'question' => 'Apakah pendaftaran harus melalui SEVIMA?', 'answer' => 'Tidak. Sistem PMB ini berdiri sendiri dan dapat dikonfigurasi untuk kampus yang memakai sistem akademik apa pun.'],
            ['category' => 'kelas', 'question' => 'Apakah tersedia kelas untuk karyawan?', 'answer' => 'Tersedia pilihan kelas malam, Sabtu, dan hybrid sesuai program studi dan kampus yang dibuka.'],
            ['category' => 'biaya', 'question' => 'Apakah biaya sama di semua kampus?', 'answer' => 'Tidak selalu. Biaya dapat diatur per kampus, program studi, jalur, gelombang, dan kelas.'],
            ['category' => 'ai', 'question' => 'Bagaimana AI menjawab pertanyaan calon mahasiswa?', 'answer' => 'AI membaca data PMB dari API internal yang terstruktur, bukan dari dokumen bebas atau API vendor tertentu.'],
        ];

        foreach ($faqs as $index => $faq) {
            DB::table('pmb_faqs')->updateOrInsert(
                ['institution_id' => $institutionId, 'question' => $faq['question']],
                [
                    ...$faq,
                    'institution_id' => $institutionId,
                    'campus_id' => $campusIds['kuningan'] ?? null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $this->now,
                    'created_at' => $this->now,
                ],
            );
        }
    }
}
