<?php

namespace Database\Seeders;

use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@pmb.test'],
            [
                'name' => 'Admin PMB',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
        );

        CampusSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'campus_name' => 'Universitas Paramadina',
                'pmb_tagline' => 'Penerimaan Mahasiswa Baru 2026',
                'hero_description' => 'Pendidikan berkualitas, berwawasan global dan berbasis nilai-nilai Islam, untuk membentuk pemimpin masa depan.',
                'address' => 'Jl. Gatot Subroto Kav. 97, Jakarta Selatan',
                'website' => 'https://paramadina.ac.id',
                'facebook' => 'https://facebook.com/paramadina',
                'instagram' => 'https://instagram.com/paramadina',
                'twitter' => 'https://twitter.com/paramadina',
                'linkedin' => 'https://linkedin.com/school/paramadina',
                'youtube' => 'https://youtube.com/@paramadina',
                'fax' => '(021) 7918 1188',
                'phone' => '(021) 7918 1181',
            ],
        );

        $this->call(PmbLandingContentSeeder::class);
        $this->call(PmbInformationSectionSeeder::class);
        $this->call(TuitionFeeSeeder::class);
        $this->call(StandalonePmbSeeder::class);
        $this->call(AdmissionPathSeeder::class);
        $this->call(PmbCascadeDemoSeeder::class);
        $this->call(StudentPortalUserSeeder::class);
    }
}
