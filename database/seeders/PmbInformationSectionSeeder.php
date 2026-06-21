<?php

namespace Database\Seeders;

use App\Models\PmbInformationSection;
use Illuminate\Database\Seeder;

class PmbInformationSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'program_level' => 'Umum',
                'category' => 'kontak',
                'title' => 'Media Sosial & Informasi Kontak Umum',
                'items' => [
                    'Website: www.paramadina.ac.id',
                    'Instagram: @universitas_paramadina / @univ.paramadina_cikarang',
                    'Facebook & X (Twitter): Paramadina',
                    'WhatsApp Info Pendaftaran: 0815-918-1190 (Yusup) / bit.ly/infoparamadina',
                ],
            ],
            [
                'program_level' => 'Umum',
                'category' => 'kontak',
                'title' => 'Informasi Pendaftaran Online',
                'items' => [
                    'admission.paramadina.ac.id',
                    'https://linktr.ee/PMB2025',
                ],
            ],
            [
                'program_level' => 'S1',
                'category' => 'info-program',
                'title' => 'Program Sarjana (S1) - Universitas Paramadina',
                'subtitle' => 'Status Akreditasi: Baik Sekali',
                'items' => [
                    'Manajemen',
                    'Ilmu Komunikasi',
                    'Hubungan Internasional',
                    'Psikologi',
                    'Falsafah dan Agama',
                    'Teknik Informatika',
                    'Desain Produk, Craft, & Fashion',
                    'Desain Komunikasi Visual',
                ],
            ],
            [
                'program_level' => 'S1',
                'category' => 'lokasi-kampus',
                'title' => 'Lokasi Kampus Program Sarjana',
                'items' => [
                    'Kampus Cipayung, Jakarta Timur - Jl. Raya Mabes Hankam Kav 9, Setu, Cipayung, Jakarta Timur 13880. Hotline Pendaftaran: 0815-818-1186 / 0815-918-1190',
                    'Kampus Cikarang, Bekasi - Distrik 2 Meikarta, Cikarang - Bekasi. Hotline Pendaftaran: 0815-918-1192',
                ],
            ],
            [
                'program_level' => 'S1',
                'category' => 'biaya',
                'title' => 'Rincian Biaya Kuliah Program Sarjana',
                'subtitle' => 'Gelombang 1 & 2',
                'items' => [
                    'Biaya Pendaftaran: Rp300.000',
                    'Kampus Cipayung Gelombang 1: Angsuran 6x Rp1.450.000; Biaya / Semester Rp8.700.000',
                    'Kampus Cipayung Gelombang 2: Angsuran 6x Rp1.550.000; Biaya / Semester Rp9.300.000',
                    'Khusus Prodi Falsafah dan Agama (Kampus Cipayung): Angsuran 6x Rp413.000; Biaya / Semester Rp2.475.000',
                    'Kampus Cikarang Gelombang 1: Angsuran 6x Rp1.150.000; Biaya / Semester Rp6.900.000',
                    'Kampus Cikarang Gelombang 2: Angsuran 6x Rp1.250.000; Biaya / Semester Rp7.500.000',
                ],
            ],
            [
                'program_level' => 'S1',
                'category' => 'jadwal',
                'title' => 'Jadwal Pendaftaran Mahasiswa Baru S1',
                'items' => [
                    'Gelombang 1: 30 Oktober 2025 - 28 Agustus 2026',
                    'Gelombang 2: 29 Agustus - 18 September 2026',
                ],
            ],
            [
                'program_level' => 'S2',
                'category' => 'info-program',
                'title' => 'Pilihan Program Studi Program Magister',
                'items' => [
                    'Magister Manajemen',
                    'Magister Komunikasi',
                    'Magister Hubungan Internasional',
                    'Magister Psikologi',
                    'Magister Ilmu Agama Islam',
                ],
            ],
            [
                'program_level' => 'S2',
                'category' => 'lokasi-kampus',
                'title' => 'Lokasi Kampus Program Magister',
                'items' => [
                    'Kampus Cipayung, Jakarta Timur - Jl. Raya Mabes Hankam Kav 9, Setu, Cipayung, Jakarta Timur 13880. Hotline: 0815-818-1186 / 0815-918-1190',
                    'Kampus Kuningan, Jakarta Selatan - Trinity Tower Lt. 45, Jalan H.R Rasuna Said Kav C22, Jakarta Selatan, 12940. Hotline: 0815-918-1192',
                    'Kampus Cikarang, Bekasi - Distrik 2 Meikarta, Cikarang - Bekasi. Hotline: 0815-918-1192',
                ],
            ],
            [
                'program_level' => 'S2',
                'category' => 'info-program',
                'title' => 'Keunggulan Universitas Paramadina',
                'items' => [
                    'Akreditasi Baik Sekali',
                    'Waktu perkuliahan yang fleksibel: pilihan kelas Malam, Sabtu Pagi, dan Sabtu Siang.',
                    'Dosen dengan keahlian dan kompetensi: latar belakang akademisi, praktisi, dan para pakar di bidangnya.',
                    'Tidak ada batasan usia dan tahun ijazah.',
                    'Tersedia jalur beasiswa: jurnalis, guru, kerja sama perusahaan dan pemerintah.',
                    'Terdapat 3 lokasi kampus strategis: Kampus Cipayung, Kampus Kuningan, Kampus Cikarang.',
                    'Networking: kuliah umum yang dapat menambah wawasan dan relasi mahasiswa.',
                    'Metode pembelajaran mix learning: perkuliahan dilakukan menggunakan metode online dan offline.',
                ],
            ],
            [
                'program_level' => 'S2',
                'category' => 'biaya',
                'title' => 'Rincian Biaya Kuliah Program Magister',
                'items' => [
                    'Biaya Pendaftaran: Rp500.000',
                    'Kampus Cipayung - Magister Manajemen: Angsuran 6x Rp1.852.000; Biaya / Semester Rp11.112.000; Biaya Sertifikasi Wajib Est. Rp3.000.000 - Rp5.000.000',
                    'Kampus Cipayung - Magister Komunikasi / Magister Psikologi / Magister Hubungan Internasional: Angsuran 6x Rp1.505.000; Biaya / Semester Rp9.030.000',
                    'Kampus Cipayung - Magister Ilmu Agama Islam: Angsuran 6x Rp1.467.000; Biaya / Semester Rp8.800.000',
                    'Kampus Kuningan - Magister Manajemen: Angsuran 6x Rp2.625.000; Biaya / Semester Rp15.750.000; Biaya Sertifikasi Wajib Est. Rp3.000.000 - Rp5.000.000',
                    'Kampus Kuningan - Magister Komunikasi / Magister Psikologi / Magister Hubungan Internasional: Angsuran 6x Rp1.943.000; Biaya / Semester Rp11.658.000',
                    'Kampus Cikarang - Magister Manajemen: Angsuran 6x Rp1.750.000; Biaya / Semester Rp10.500.000; Biaya Sertifikasi Wajib Est. Rp3.000.000 - Rp5.000.000',
                    'Kampus Cikarang - Magister Komunikasi: Angsuran 6x Rp1.400.000; Biaya / Semester Rp8.400.000',
                ],
            ],
            [
                'program_level' => 'S2',
                'category' => 'jadwal',
                'title' => 'Jadwal Program Magister',
                'items' => [
                    'Semester Genap: 30 Oktober 2025 - 5 April 2026',
                    'Semester Gasal: 11 April - 18 September 2026',
                ],
            ],
            [
                'program_level' => 'S2',
                'category' => 'kelas',
                'title' => 'Pilihan Kelas Program Magister',
                'items' => [
                    'Kelas A: Senin - Jumat (18.30 - 21.00 WIB)',
                    'Kelas B: Sabtu Pagi (07.00 - 12.15 WIB)',
                    'Kelas C: Sabtu Siang (14.00 - 19.30 WIB)',
                    'Waktu perkuliahan dapat berubah menyesuaikan kebijakan program studi.',
                ],
            ],
            [
                'program_level' => 'S3',
                'category' => 'info-program',
                'title' => 'Program Doktor (S3) Manajemen & Bisnis',
                'subtitle' => 'Leading with Knowledge, Impacting Sustainability',
                'body' => 'Program Doktor Ilmu Manajemen dan Bisnis mencetak pemimpin, peneliti, dan praktisi unggul dengan perspektif manajemen berkelanjutan.',
            ],
            [
                'program_level' => 'S3',
                'category' => 'info-program',
                'title' => 'Peminatan Strategis Program Doktor',
                'items' => [
                    'Pemasaran Berkelanjutan',
                    'Keuangan Berkelanjutan',
                    'SDM Berkelanjutan',
                    'Manajemen Komunikasi Berkelanjutan',
                ],
            ],
            [
                'program_level' => 'S3',
                'category' => 'biaya',
                'title' => 'Biaya Perkuliahan Program Doktor',
                'items' => [
                    'Uang Pendaftaran: Rp750.000',
                    'Semester 7 dan seterusnya: Rp10.000.000 tanpa mata kuliah',
                    'Reguler: 6x angsuran Rp3.612.000 atau Rp21.666.667 / semester',
                    'Early Bird 20%: 6x angsuran Rp2.889.000 atau Rp17.333.333 / semester',
                    'Alumni 30%: 6x angsuran Rp2.528.000 atau Rp15.166.667 / semester',
                    'Prestasi 50%: 6x angsuran Rp1.806.000 atau Rp10.833.333 / semester',
                ],
            ],
            [
                'program_level' => 'S3',
                'category' => 'kurikulum',
                'title' => 'Struktur Mata Kuliah Program Doktor',
                'items' => [
                    'Semester 1: Metode Riset, Filsafat Ilmu Manajemen, Teori Organisasi',
                    'Semester 2: Penulisan Ilmiah, Manajemen Stratejik Berkelanjutan, Riset Literatur, serta mata kuliah peminatan masing-masing.',
                    'Semester 3: Seminar Proposal',
                    'Semester 4: Publikasi Jurnal Nasional Sinta 2 atau International Conference Bereputasi, Seminar Hasil Penelitian',
                    'Semester 5: Publikasi Internasional Bereputasi',
                    'Semester 6: Disertasi',
                ],
            ],
            [
                'program_level' => 'S3',
                'category' => 'syarat',
                'title' => 'Syarat Masuk Program Doktor',
                'items' => [
                    'Ijazah Magister (S2) terakreditasi',
                    'Transkrip nilai minimal IPK 3',
                    'TPA Bappenas minimal 475 (skor minimum level doktoral)',
                    'Tes Bahasa Inggris: TOEFL minimal 475 / IELTS minimal 6',
                    'Wawancara Akademik: evaluasi proposal riset awal, penilaian kapasitas penelitian, dan kesiapan akademik.',
                ],
            ],
            [
                'program_level' => 'S3',
                'category' => 'kelas',
                'title' => 'Waktu Perkuliahan & Lokasi Kampus Program Doktor',
                'items' => [
                    'Kelas Reguler Pagi: Senin - Jumat, Jam 09.00 - 16.00',
                    'Kelas Weekend: Kamis & Jumat (Jam 19.00), Sabtu (Jam 07.00 - 16.00)',
                    'Lokasi: Kampus Cipayung, Jl. Raya Mabes Hankam Kav 9, Setu, Cipayung, Jakarta Timur - 13880',
                ],
            ],
        ];

        foreach ($sections as $index => $section) {
            PmbInformationSection::query()->updateOrCreate(
                [
                    'program_level' => $section['program_level'],
                    'category' => $section['category'],
                    'title' => $section['title'],
                ],
                [
                    'subtitle' => $section['subtitle'] ?? null,
                    'body' => $section['body'] ?? null,
                    'items' => $section['items'] ?? [],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
