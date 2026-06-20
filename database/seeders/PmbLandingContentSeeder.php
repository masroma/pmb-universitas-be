<?php

namespace Database\Seeders;

use App\Models\PmbBenefit;
use App\Models\PmbRegistrationFlow;
use App\Models\PmbRegistrationPath;
use App\Models\PmbStudyProgram;
use App\Services\SevimaPmbSyncService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

class PmbLandingContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBenefits();
        $this->seedStudyPrograms();
        $this->seedRegistrationPaths();
        $this->seedRegistrationFlows();

        if ($this->hasSevimaCredentials()) {
            try {
                app(SevimaPmbSyncService::class)->sync(false);
            } catch (Throwable $exception) {
                $this->command?->warn('Sync Data PMB SEVIMA gagal: '.$exception->getMessage());
            }
        }
    }

    private function seedBenefits(): void
    {
        $benefits = [
            ['icon' => 'A', 'title' => 'Akreditasi Baik Sekali', 'description' => 'Kualitas pendidikan terjamin dengan standar nasional'],
            ['icon' => 'W', 'title' => 'Waktu perkuliahan yang fleksibel', 'description' => 'Pilihan kelas Malam, Sabtu Pagi, dan Sabtu Siang.'],
            ['icon' => 'D', 'title' => 'Dosen dengan keahlian dan kompetensi', 'description' => 'Latar belakang Akademisi, Praktisi, dan para Pakar dibidangnya.'],
            ['icon' => 'U', 'title' => 'Tidak ada batasan usia', 'description' => 'Tidak ada batasan usia dan tahun ijazah.'],
            ['icon' => 'B', 'title' => 'Tersedia jalur beasiswa', 'description' => 'Jurnalis, Guru, Kerja Sama Perusahaan dan Pemerintah.'],
            ['icon' => 'K', 'title' => 'Terdapat 3 lokasi kampus strategis', 'description' => 'Kampus Cipayung, Kampus Kuningan, Kampus Cikarang'],
            ['icon' => 'N', 'title' => 'Networking', 'description' => 'Kuliah umum yang dapat menambah wawasan dan relasi mahasiswa.', 'is_italic' => true],
            ['icon' => 'M', 'title' => 'Metode Pembelajaran', 'emphasis' => 'Mix Learning', 'description' => 'Perkuliahan dilakukan menggunakan metode online dan offline.'],
        ];

        foreach ($benefits as $index => $benefit) {
            PmbBenefit::query()->updateOrCreate(
                ['title' => $benefit['title']],
                [
                    ...$benefit,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedStudyPrograms(): void
    {
        $programs = [
            ['level' => 'S1', 'title' => 'Manajemen', 'accreditation' => 'Unggul'],
            ['level' => 'S1', 'title' => 'Ilmu Hubungan Internasional', 'accreditation' => 'B'],
            ['level' => 'S1', 'title' => 'Ilmu Komunikasi', 'accreditation' => 'Baik Sekali'],
            ['level' => 'S1', 'title' => 'Psikologi', 'accreditation' => 'Baik Sekali'],
            ['level' => 'S1', 'title' => 'Falsafah Dan Agama', 'accreditation' => 'Unggul'],
            ['level' => 'S1', 'title' => 'Teknik Informatika', 'accreditation' => 'Baik Sekali'],
            ['level' => 'S1', 'title' => 'Desain Produk', 'accreditation' => 'Baik'],
            ['level' => 'S1', 'title' => 'Desain Komunikasi Visual', 'accreditation' => 'A'],
            ['level' => 'S2', 'title' => 'Manajemen', 'accreditation' => 'B'],
            ['level' => 'S2', 'title' => 'Ilmu Hubungan Internasional', 'accreditation' => 'Baik Sekali'],
            ['level' => 'S2', 'title' => 'Ilmu Komunikasi', 'accreditation' => 'Baik Sekali'],
            ['level' => 'S2', 'title' => 'Psikologi', 'accreditation' => '---'],
            ['level' => 'S2', 'title' => 'Ilmu Agama Islam', 'accreditation' => 'Baik Sekali'],
            ['level' => 'S3', 'title' => 'Ilmu Manajemen', 'accreditation' => '---'],
        ];

        foreach ($programs as $index => $program) {
            PmbStudyProgram::query()->updateOrCreate(
                [
                    'level' => $program['level'],
                    'title' => $program['title'],
                ],
                [
                    ...$program,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedRegistrationPaths(): void
    {
        $paths = [
            ['title' => 'Prestasi', 'period' => '1 Nov 2025 - 28 Agt 2026', 'fee' => 'Rp. 300.000 - Rp. 500.000'],
            ['title' => 'Rekognisi Pembelajaran Lampau', 'period' => '1 Apr - 28 Agt 2026', 'fee' => 'Rp. 300.000'],
            ['title' => 'Beasiswa', 'period' => '1 Mei - 28 Agt 2026', 'fee' => 'Rp. 0 - Rp. 750.000'],
            ['title' => 'Program Magister', 'period' => '1 Mei - 19 Jun 2026', 'fee' => 'Rp. 500.000'],
            ['title' => 'Reguler', 'period' => '30 Okt 2025 - 27 Sep 2026', 'fee' => 'Rp. 300.000 - Rp. 750.000'],
        ];

        foreach ($paths as $index => $path) {
            PmbRegistrationPath::query()->updateOrCreate(
                ['title' => $path['title']],
                [
                    ...$path,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedRegistrationFlows(): void
    {
        $flows = [
            [
                'title' => 'Program Sarjana',
                'description' => 'Alur untuk calon mahasiswa baru jenjang S1, mulai dari akun pendaftaran sampai orientasi mahasiswa baru.',
                'accent_class' => 'bg-blue-700',
                'steps' => [
                    ['title' => 'Buat akun pendaftaran', 'description' => 'Daftar akun melalui portal admission.paramadina.ac.id.'],
                    ['title' => 'Bayar biaya pendaftaran', 'description' => 'Lakukan pembayaran Rp. 300.000 melalui virtual account yang muncul pada akun pendaftaran.'],
                    ['title' => 'Lengkapi data dan berkas', 'description' => 'Login kembali untuk mengisi biodata dan mengunggah berkas pendaftaran.'],
                    ['title' => 'Dapatkan kartu peserta ujian', 'description' => 'Calon mahasiswa memperoleh kartu peserta ujian yang berisi jadwal dan informasi seleksi.'],
                    ['title' => 'Ikuti seleksi', 'description' => 'Mengikuti tes seleksi dan wawancara sesuai jadwal pada kartu peserta.'],
                    ['title' => 'Cek pengumuman', 'description' => 'Hasil seleksi dapat dilihat melalui akun pendaftaran masing-masing.'],
                    ['title' => 'Daftar ulang', 'description' => 'Jika dinyatakan lulus, lanjutkan pembayaran daftar ulang dan generate virtual account.'],
                    ['title' => 'Upload berkas daftar ulang', 'description' => 'Unduh surat pernyataan mahasiswa baru, lengkapi, lalu upload bersama ijazah, KK, dan dokumen pendukung.'],
                    ['title' => 'Terima NIM', 'description' => 'Tim PMB melakukan verifikasi, lalu Nomor Induk Mahasiswa akan diterbitkan.'],
                    ['title' => 'Akses sistem akademik', 'description' => 'Mahasiswa baru menerima informasi akun sistem akademik seperti g-suite, edlink, dan simpul.'],
                    ['title' => 'Ikuti orientasi', 'description' => 'Mengikuti Graha Mahardika Paramadina atau orientasi mahasiswa baru.'],
                ],
            ],
            [
                'title' => 'Program Magister',
                'description' => 'Alur untuk calon mahasiswa baru jenjang S2, dengan tahapan seleksi dan administrasi pascasarjana.',
                'accent_class' => 'bg-red-800',
                'steps' => [
                    ['title' => 'Buat akun pendaftaran', 'description' => 'Daftar akun melalui portal admission.paramadina.ac.id.'],
                    ['title' => 'Bayar biaya pendaftaran', 'description' => 'Lakukan pembayaran Rp. 500.000 melalui virtual account pada akun pendaftaran.'],
                    ['title' => 'Lengkapi biodata dan dokumen', 'description' => 'Isi biodata dan unggah dokumen seperti ijazah S1, transkrip nilai, KK, KTP, dan dokumen pendukung lainnya.'],
                    ['title' => 'Ikuti tes seleksi', 'description' => 'Masuk ke menu login CBT dan lakukan tes sesuai instruksi yang tersedia.'],
                    ['title' => 'Cek hasil seleksi', 'description' => 'Hasil tes diproses dalam 1x24 jam hari kerja. Beberapa program dapat melalui tahap wawancara.'],
                    ['title' => 'Daftar ulang', 'description' => 'Jika lulus, lanjutkan pembayaran daftar ulang melalui menu keuangan pada akun pendaftaran.'],
                    ['title' => 'Generate NIM', 'description' => 'Nomor Induk Mahasiswa diterbitkan setelah administrasi dan pembayaran selesai.'],
                    ['title' => 'Akses sistem akademik', 'description' => 'Mahasiswa baru menerima informasi akun sistem akademik seperti g-suite, edlink, dan simpul.'],
                    ['title' => 'Ikuti orientasi', 'description' => 'Mengikuti Graha Mahardika Paramadina atau orientasi mahasiswa baru.'],
                    ['title' => 'Resmi menjadi mahasiswa', 'description' => 'Selamat, kamu telah menjadi mahasiswa baru Universitas Paramadina.'],
                ],
            ],
        ];

        foreach ($flows as $flowIndex => $flowData) {
            $steps = $flowData['steps'];
            unset($flowData['steps']);

            $flow = PmbRegistrationFlow::query()->updateOrCreate(
                ['title' => $flowData['title']],
                [
                    ...$flowData,
                    'sort_order' => $flowIndex + 1,
                    'is_active' => true,
                ],
            );

            foreach ($steps as $stepIndex => $step) {
                $flow->steps()->updateOrCreate(
                    ['title' => $step['title']],
                    [
                        ...$step,
                        'sort_order' => $stepIndex + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    private function syncStudyProgramsFromSevima(): void
    {
        $this->sevimaItems('/siakadcloud/v1/program-studi')->each(function (array $item, int $index): void {
            $title = $this->firstFilled($item, [
                'nama_program_studi',
                'nama_prodi',
                'program_studi',
                'nama',
                'name',
            ]);

            if (! $title) {
                return;
            }

            $this->updateStudyProgramFromSevima([
                'sevima_id' => $this->externalId($item),
                'level' => $this->firstFilled($item, ['jenjang', 'nama_jenjang', 'kode_jenjang', 'id_jenjang', 'strata']) ?: null,
                'title' => $title,
                'accreditation' => $this->accreditationText($this->firstFilled($item, ['akreditasi', 'peringkat_akreditasi', 'status_akreditasi'])),
                'sort_order' => $index + 1,
                'is_active' => true,
                'raw_payload' => $item,
            ]);
        });
    }

    private function syncRegistrationPathsFromSevima(): void
    {
        $periods = $this->sevimaItems('/siakadcloud/v1/periode-pendaftaran', [
            'f-periode_akademik' => config('sevima.periode_akademik'),
            'o-id' => 'desc',
        ]);

        if ($periods->isEmpty()) {
            return;
        }

        $categories = [
            'Prestasi' => [],
            'Rekognisi Pembelajaran Lampau' => [],
            'Beasiswa' => [],
            'Program Magister' => [],
            'Reguler' => [],
        ];

        $periods
            ->filter(fn (array $item): bool => $this->firstFilled($item, ['is_deleted']) !== '1')
            ->each(function (array $item) use (&$categories): void {
                $categories[$this->pathCategory($item)][] = $item;
            });

        foreach ($categories as $title => $items) {
            if ($items === []) {
                continue;
            }

            $startsAt = collect($items)
                ->map(fn (array $item): ?Carbon => $this->dateValue($item, ['tanggal_awal_pendaftaran', 'tanggal_mulai', 'tgl_mulai', 'mulai', 'start_date']))
                ->filter()
                ->sort()
                ->first();
            $endsAt = collect($items)
                ->map(fn (array $item): ?Carbon => $this->dateValue($item, ['tanggal_akhir_pendaftaran', 'tanggal_selesai', 'tgl_selesai', 'selesai', 'end_date']))
                ->filter()
                ->sortDesc()
                ->first();
            $existing = PmbRegistrationPath::query()->where('title', $title)->first();

            $this->updateModelBySevimaIdOrTitle(PmbRegistrationPath::class, [
                'sevima_id' => 'landing-'.strtolower(str_replace(' ', '-', $title)),
                'title' => $title,
                'period' => $this->periodText($startsAt, $endsAt) ?: $existing?->period,
                'fee' => $existing?->fee,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'sort_order' => $existing?->sort_order ?? 0,
                'is_active' => true,
                'raw_payload' => array_values($items),
            ]);
        }
    }

    private function hasSevimaCredentials(): bool
    {
        return filled(config('sevima.app_key')) && filled(config('sevima.secret_key'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function sevimaItems(string $endpoint, array $query = []): Collection
    {
        try {
            $response = Http::baseUrl(config('sevima.base_url'))
                ->acceptJson()
                ->withHeaders([
                    'X-App-Key' => config('sevima.app_key'),
                    'X-Secret-Key' => config('sevima.secret_key'),
                ])
                ->get($endpoint, $query);

            if (! $response->successful()) {
                $this->command?->warn("SEVIMA {$endpoint} gagal: HTTP {$response->status()}");

                return collect();
            }

            return $this->extractItems($response->json());
        } catch (Throwable $exception) {
            $this->command?->warn("SEVIMA {$endpoint} gagal: {$exception->getMessage()}");

            return collect();
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function extractItems(mixed $payload): Collection
    {
        if (! is_array($payload)) {
            return collect();
        }

        foreach (['data.data', 'data.items', 'data.records', 'items', 'records', 'data'] as $key) {
            $candidate = data_get($payload, $key);

            if (is_array($candidate) && array_is_list($candidate)) {
                return collect($candidate)->filter(fn (mixed $item): bool => is_array($item))->values();
            }
        }

        if (array_is_list($payload)) {
            return collect($payload)->filter(fn (mixed $item): bool => is_array($item))->values();
        }

        return collect();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $attributes
     */
    private function updateModelBySevimaIdOrTitle(string $modelClass, array $attributes): void
    {
        $query = $modelClass::query();
        $model = null;

        if (filled($attributes['sevima_id'] ?? null)) {
            $model = (clone $query)->where('sevima_id', $attributes['sevima_id'])->first();
        }

        $model ??= (clone $query)->where('title', $attributes['title'])->first();

        if ($model) {
            $model->fill($attributes)->save();

            return;
        }

        $modelClass::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function updateStudyProgramFromSevima(array $attributes): void
    {
        $query = PmbStudyProgram::query();
        $program = null;

        if (filled($attributes['sevima_id'] ?? null)) {
            $program = (clone $query)->where('sevima_id', $attributes['sevima_id'])->first();
        }

        $program ??= (clone $query)
            ->where('level', $attributes['level'])
            ->where('title', $attributes['title'])
            ->first();

        if ($program) {
            $program->fill($attributes)->save();

            return;
        }

        PmbStudyProgram::query()->create($attributes);
    }

    private function externalId(array $item): ?string
    {
        return $this->firstFilled($item, ['id', 'id_program_studi', 'id_jalur_pendaftaran', 'kode_program_studi', 'kode']);
    }

    private function firstFilled(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($item, $key) ?? data_get($item, 'attributes.'.$key);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function dateValue(array $item, array $keys): ?Carbon
    {
        $value = $this->firstFilled($item, $keys);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function periodText(?Carbon $startsAt, ?Carbon $endsAt): ?string
    {
        if (! $startsAt || ! $endsAt) {
            return null;
        }

        return $startsAt->translatedFormat('j M Y').' - '.$endsAt->translatedFormat('j M Y');
    }

    private function feeText(?string $fee): ?string
    {
        if (! filled($fee)) {
            return null;
        }

        if (str_contains(strtolower($fee), 'rp')) {
            return $fee;
        }

        $number = (int) preg_replace('/\D+/', '', $fee);

        if ($number <= 0) {
            return $fee;
        }

        return 'Rp. '.number_format($number, 0, ',', '.');
    }

    private function accreditationText(?string $accreditation): string
    {
        return match ($accreditation) {
            'U' => 'Unggul',
            'S' => 'Baik Sekali',
            default => $accreditation ?: '---',
        };
    }

    private function pathCategory(array $item): string
    {
        $text = strtolower(implode(' ', array_filter([
            $this->firstFilled($item, ['nama_periode_pendaftaran']),
            $this->firstFilled($item, ['jalur_pendaftaran', 'nama_jalur_pendaftaran']),
            $this->firstFilled($item, ['sistem_kuliah']),
        ])));

        if (str_contains($text, 'beasiswa')) {
            return 'Beasiswa';
        }

        if (str_contains($text, 'rpl') || str_contains($text, 'rekognisi') || str_contains($text, 'lampau')) {
            return 'Rekognisi Pembelajaran Lampau';
        }

        if (str_contains($text, 'magister') || str_contains($text, 'pasca') || str_contains($text, 's2')) {
            return 'Program Magister';
        }

        if (str_contains($text, 'prestasi') || str_contains($text, 'rapor')) {
            return 'Prestasi';
        }

        return 'Reguler';
    }
}
