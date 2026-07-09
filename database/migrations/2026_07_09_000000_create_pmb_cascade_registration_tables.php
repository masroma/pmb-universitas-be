<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pmb_synced_registration_periods')) {
            Schema::create('pmb_synced_registration_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('sevima_id')->unique();
            $table->string('nama_periode_pendaftaran')->nullable();
            $table->date('tanggal_awal_pendaftaran')->nullable();
            $table->date('tanggal_akhir_pendaftaran')->nullable();
            $table->string('status_periode_pendaftaran')->nullable();
            $table->string('id_status_periode_pendaftaran', 5)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('tanggal_akhir_pendaftaran', 'pmb_sync_periods_end_idx');
            $table->index('id_status_periode_pendaftaran', 'pmb_sync_periods_status_idx');
            });
        } else {
            Schema::table('pmb_synced_registration_periods', function (Blueprint $table): void {
                if (! $this->indexExists('pmb_synced_registration_periods', 'pmb_sync_periods_end_idx')) {
                    $table->index('tanggal_akhir_pendaftaran', 'pmb_sync_periods_end_idx');
                }
                if (! $this->indexExists('pmb_synced_registration_periods', 'pmb_sync_periods_status_idx')) {
                    $table->index('id_status_periode_pendaftaran', 'pmb_sync_periods_status_idx');
                }
            });
        }

        if (! Schema::hasTable('pmb_open_study_programs')) {
            Schema::create('pmb_open_study_programs', function (Blueprint $table): void {
            $table->id();
            $table->string('sevima_id')->nullable();
            $table->unsignedBigInteger('sevima_record_id')->nullable();
            $table->string('jenjang_program_studi', 10)->nullable();
            $table->unsignedInteger('id_program_studi')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('sistem_kuliah')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('id_periode_pendaftaran')->nullable();
            $table->string('nama_periode_pendaftaran')->nullable();
            $table->unsignedInteger('id_jalur_pendaftaran')->nullable();
            $table->string('jalur_pendaftaran')->nullable();
            $table->unsignedInteger('id_gelombang')->nullable();
            $table->string('gelombang')->nullable();
            $table->unsignedInteger('registration_fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['jenjang_program_studi', 'id_program_studi'], 'pmb_open_programs_level_prodi_idx');
            $table->index(['jenjang_program_studi', 'id_program_studi', 'lokasi'], 'pmb_open_programs_level_prodi_loc_idx');
            $table->index('id_periode_pendaftaran', 'pmb_open_programs_period_idx');
            $table->index('nama_periode_pendaftaran', 'pmb_open_programs_class_idx');
            $table->index('id_jalur_pendaftaran', 'pmb_open_programs_path_idx');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_open_study_programs');
        Schema::dropIfExists('pmb_synced_registration_periods');
    }
};
