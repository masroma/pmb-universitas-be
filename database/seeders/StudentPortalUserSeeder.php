<?php

namespace Database\Seeders;

use App\Models\PmbLocalApplication;
use App\Models\PmbPeriod;
use App\Models\PmbSevimaRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentPortalUserSeeder extends Seeder
{
    public function run(): void
    {
        $studentEmails = [
            'mahasiswa@pmb.test',
            'mahasiswa@pmb.text',
        ];

        $academicPeriod = PmbPeriod::query()
            ->where('is_active', true)
            ->orderByDesc('sevima_id')
            ->first();

        $programOption = PmbSevimaRecord::query()
            ->where('entity_type', 'program-studi-dibuka')
            ->where('is_active', true)
            ->orderBy('parent_sevima_id')
            ->orderBy('title')
            ->first();

        $registrationPeriod = PmbSevimaRecord::query()
            ->where('entity_type', 'periode-pendaftaran')
            ->where('sevima_id', $programOption?->parent_sevima_id)
            ->first();

        foreach ($studentEmails as $email) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Mahasiswa Demo',
                    'phone' => '081234567890',
                    'role' => 'mahasiswa',
                    'password' => Hash::make('password123'),
                ],
            );

            $application = PmbLocalApplication::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => PmbLocalApplication::STATUS_DRAFT,
                    'academic_period_id' => $academicPeriod?->sevima_id,
                    'academic_period_name' => $academicPeriod?->name,
                    'registration_period_id' => $registrationPeriod?->sevima_id,
                    'registration_period_name' => $registrationPeriod?->title ?: $registrationPeriod?->period,
                    'program_option_id' => $programOption?->id,
                    'study_program_id' => $this->firstFilled($programOption?->raw_payload ?? [], ['id_program_studi', 'kode_program_studi']),
                    'study_program_name' => $programOption?->title ?: $this->firstFilled($programOption?->raw_payload ?? [], ['program_studi', 'nama_program_studi', 'nama_prodi']),
                    'registration_path_id' => $this->firstFilled($programOption?->raw_payload ?? [], ['id_jalur_pendaftaran', 'kode_jalur_pendaftaran']),
                    'registration_path_name' => $this->firstFilled($programOption?->raw_payload ?? [], ['jalur_pendaftaran', 'nama_jalur_pendaftaran']),
                    'study_system_id' => $this->firstFilled($programOption?->raw_payload ?? [], ['id_sistem_kuliah', 'kode_sistem_kuliah']),
                    'study_system_name' => $this->firstFilled($programOption?->raw_payload ?? [], ['sistem_kuliah', 'nama_sistem_kuliah']),
                    'name' => 'Mahasiswa Demo',
                    'email' => $email,
                    'phone' => '081234567890',
                    'gender' => 'Laki-laki',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '2002-06-20',
                    'nik' => '3174012006020001',
                    'address' => 'Jl. Demo Mahasiswa No. 10, Jakarta Selatan',
                    'city' => 'Jakarta Selatan',
                    'province' => 'DKI Jakarta',
                    'country' => 'Indonesia',
                    'applicant_note' => 'Data demo sudah terisi. Bagian dokumen sengaja dikosongkan untuk uji coba AI reader dokumen.',
                    'submitted_at' => null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'review_note' => null,
                ],
            );

            $application->documents()->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (filled($payload[$key] ?? null)) {
                return (string) $payload[$key];
            }
        }

        return null;
    }
}
