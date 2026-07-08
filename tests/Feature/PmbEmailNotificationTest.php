<?php

namespace Tests\Feature;

use App\Mail\Pmb\AccountRegisteredMail;
use App\Mail\Pmb\ApplicationRejectedMail;
use App\Mail\Pmb\ApplicationSubmittedMail;
use App\Mail\Pmb\ApplicationVerifiedMail;
use App\Models\CampusSetting;
use App\Models\PmbLocalApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesStandalonePmbFixtures;
use Tests\TestCase;

class PmbEmailNotificationTest extends TestCase
{
    use CreatesStandalonePmbFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CampusSetting::query()->create([
            'campus_name' => 'Universitas Test',
            'pmb_tagline' => 'Penerimaan Mahasiswa Baru 2026',
            'hero_description' => 'Deskripsi hero test.',
        ]);
    }

    public function test_register_sends_account_email(): void
    {
        Mail::fake();

        $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        Mail::assertSent(AccountRegisteredMail::class, function (AccountRegisteredMail $mail): bool {
            return $mail->hasTo('budi@example.com');
        });
    }

    public function test_submit_sends_application_submitted_email(): void
    {
        Mail::fake();

        $fixture = $this->createStandalonePmbFixture();
        $user = $this->createApplicantUser('rina@example.com');
        $token = $this->apiTokenFor($user);

        $this->withToken($token)->postJson('/api/registration', [
            'academic_period_id' => $fixture['period_id'],
            'registration_period_id' => $fixture['wave_id'],
            'program_option_id' => $fixture['registration_option_id'],
            'name' => 'Rina Wijaya',
            'email' => 'rina@example.com',
            'phone' => '081299988877',
            'gender' => 'Perempuan',
            'birth_place' => 'Jakarta',
            'birth_date' => '2005-01-01',
            'nik' => '3174010101050001',
            'address' => 'Jl. Contoh No. 1',
        ])->assertOk();

        $application = PmbLocalApplication::query()->where('user_id', $user->id)->firstOrFail();
        $this->seedRequiredDocuments($application, $token);

        $this->withToken($token)->postJson('/api/registration/submit')->assertOk();

        Mail::assertSent(ApplicationSubmittedMail::class, function (ApplicationSubmittedMail $mail): bool {
            return $mail->hasTo('rina@example.com');
        });
    }

    public function test_admin_verify_sends_verified_email(): void
    {
        Mail::fake();

        $admin = User::query()->create([
            'name' => 'Admin PMB',
            'email' => 'admin@pmb.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        $application = PmbLocalApplication::query()->create([
            'user_id' => User::query()->create([
                'name' => 'Andi',
                'email' => 'andi@example.com',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ])->id,
            'status' => PmbLocalApplication::STATUS_SUBMITTED,
            'name' => 'Andi',
            'email' => 'andi@example.com',
            'phone' => '081200000001',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->put(route('admin.local-applications.status.update', $application), [
            'status' => PmbLocalApplication::STATUS_VERIFIED,
            'review_note' => 'Dokumen lengkap.',
        ])->assertRedirect();

        Mail::assertSent(ApplicationVerifiedMail::class, function (ApplicationVerifiedMail $mail): bool {
            return $mail->hasTo('andi@example.com');
        });
    }

    public function test_admin_reject_sends_rejected_email(): void
    {
        Mail::fake();

        $admin = User::query()->create([
            'name' => 'Admin PMB',
            'email' => 'admin@pmb.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        $application = PmbLocalApplication::query()->create([
            'user_id' => User::query()->create([
                'name' => 'Sari',
                'email' => 'sari@example.com',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
            ])->id,
            'status' => PmbLocalApplication::STATUS_SUBMITTED,
            'name' => 'Sari',
            'email' => 'sari@example.com',
            'phone' => '081200000002',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->put(route('admin.local-applications.status.update', $application), [
            'status' => PmbLocalApplication::STATUS_REJECTED,
            'review_note' => 'Foto tidak jelas.',
        ])->assertRedirect();

        Mail::assertSent(ApplicationRejectedMail::class, function (ApplicationRejectedMail $class): bool {
            return $class->hasTo('sari@example.com');
        });
    }

    private function createApplicantUser(string $email): User
    {
        return User::query()->create([
            'name' => 'Calon Mahasiswa',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);
    }

    private function apiTokenFor(User $user): string
    {
        $plainToken = str_repeat('a', 80);
        $user->forceFill(['api_token' => hash('sha256', $plainToken)])->save();

        return $user->id.'|'.$plainToken;
    }

    private function seedRequiredDocuments(PmbLocalApplication $application, string $token): void
    {
        foreach (['ktp', 'ijazah', 'foto'] as $type) {
            $file = \Illuminate\Http\UploadedFile::fake()->create($type.'.pdf', 100, 'application/pdf');

            $this->withToken($token)->post('/api/registration/documents', [
                'type' => $type,
                'document' => $file,
            ])->assertCreated();
        }
    }
}
