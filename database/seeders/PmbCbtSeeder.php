<?php

namespace Database\Seeders;

use App\Models\PmbCbtQuestion;
use App\Models\PmbCbtSetting;
use Illuminate\Database\Seeder;

class PmbCbtSeeder extends Seeder
{
    public function run(): void
    {
        PmbCbtSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Tes Seleksi PMB',
                'duration_minutes' => 30,
                'questions_per_attempt' => 10,
                'pass_score' => 60,
                'max_attempts' => 3,
                'instructions' => "1. Pastikan koneksi internet stabil selama mengerjakan tes.\n2. Durasi tes terbatas; jawaban otomatis dikirim jika waktu habis.\n3. Nilai minimal kelulusan adalah 60.\n4. Kesempatan mengerjakan maksimal 3 kali.\n5. Setelah lulus, Anda dapat melanjutkan biodata dan upload dokumen.",
                'is_active' => true,
            ],
        );

        $questions = [
            [
                'category' => 'penalaran',
                'question' => 'Jika semua mahasiswa rajin belajar dan Andi adalah mahasiswa, maka...',
                'options' => ['A' => 'Andi pasti lulus', 'B' => 'Andi rajin belajar', 'C' => 'Andi tidak pernah terlambat', 'D' => 'Andi adalah dosen'],
                'correct_option' => 'B',
            ],
            [
                'category' => 'penalaran',
                'question' => 'Manakah yang paling berbeda dari yang lain?',
                'options' => ['A' => 'Buku', 'B' => 'Majalah', 'C' => 'Koran', 'D' => 'Pensil'],
                'correct_option' => 'D',
            ],
            [
                'category' => 'penalaran',
                'question' => '2, 4, 8, 16, ... Angka berikutnya adalah?',
                'options' => ['A' => '18', 'B' => '24', 'C' => '32', 'D' => '30'],
                'correct_option' => 'C',
            ],
            [
                'category' => 'penalaran',
                'question' => 'Jika hari ini Senin, maka 3 hari kemudian adalah?',
                'options' => ['A' => 'Selasa', 'B' => 'Rabu', 'C' => 'Kamis', 'D' => 'Jumat'],
                'correct_option' => 'C',
            ],
            [
                'category' => 'numerik',
                'question' => 'Hasil dari 15% dari 200 adalah?',
                'options' => ['A' => '20', 'B' => '25', 'C' => '30', 'D' => '35'],
                'correct_option' => 'C',
            ],
            [
                'category' => 'numerik',
                'question' => 'Jika 3x + 6 = 21, maka nilai x adalah?',
                'options' => ['A' => '3', 'B' => '5', 'C' => '7', 'D' => '9'],
                'correct_option' => 'B',
            ],
            [
                'category' => 'numerik',
                'question' => 'Rata-rata dari 4, 6, 8, 10 adalah?',
                'options' => ['A' => '6', 'B' => '7', 'C' => '8', 'D' => '9'],
                'correct_option' => 'B',
            ],
            [
                'category' => 'numerik',
                'question' => 'Sebuah barang seharga Rp100.000 mendapat diskon 20%. Harga setelah diskon?',
                'options' => ['A' => 'Rp70.000', 'B' => 'Rp80.000', 'C' => 'Rp85.000', 'D' => 'Rp90.000'],
                'correct_option' => 'B',
            ],
            [
                'category' => 'bahasa',
                'question' => 'Sinonim dari kata "bijaksana" adalah?',
                'options' => ['A' => 'Ceroboh', 'B' => 'Arif', 'C' => 'Marah', 'D' => 'Malas'],
                'correct_option' => 'B',
            ],
            [
                'category' => 'bahasa',
                'question' => 'Antonim dari kata "optimis" adalah?',
                'options' => ['A' => 'Pesimis', 'B' => 'Realistis', 'C' => 'Aktif', 'D' => 'Kreatif'],
                'correct_option' => 'A',
            ],
            [
                'category' => 'bahasa',
                'question' => 'Kalimat yang baku adalah?',
                'options' => [
                    'A' => 'Saya sudah makan siang tadi.',
                    'B' => 'Saya sudah makan siang kemarin siang.',
                    'C' => 'Saya sudah makan siang tadi siang.',
                    'D' => 'Saya sudah makan siang barusan tadi.',
                ],
                'correct_option' => 'A',
            ],
            [
                'category' => 'umum',
                'question' => 'Ibu kota negara Indonesia adalah?',
                'options' => ['A' => 'Surabaya', 'B' => 'Bandung', 'C' => 'Jakarta', 'D' => 'Medan'],
                'correct_option' => 'C',
            ],
            [
                'category' => 'umum',
                'question' => 'Lambang negara Indonesia adalah?',
                'options' => ['A' => 'Garuda Pancasila', 'B' => 'Harimau', 'C' => 'Elang Jawa', 'D' => 'Komodo'],
                'correct_option' => 'A',
            ],
            [
                'category' => 'umum',
                'question' => 'Jumlah sila dalam Pancasila adalah?',
                'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
                'correct_option' => 'C',
            ],
            [
                'category' => 'umum',
                'question' => 'Hari Kemerdekaan Republik Indonesia diperingati setiap tanggal?',
                'options' => ['A' => '1 Juni', 'B' => '17 Agustus', 'C' => '28 Oktober', 'D' => '10 November'],
                'correct_option' => 'B',
            ],
        ];

        foreach ($questions as $index => $item) {
            PmbCbtQuestion::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'category' => $item['category'],
                    'options' => $item['options'],
                    'correct_option' => $item['correct_option'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
