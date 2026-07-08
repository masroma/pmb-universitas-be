<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_paths', function (Blueprint $table): void {
            if (! Schema::hasColumn('admission_paths', 'sevima_id')) {
                $table->unsignedInteger('sevima_id')->nullable()->after('institution_id');
            }

            if (! Schema::hasColumn('admission_paths', 'jenis_pendaftaran_id')) {
                $table->string('jenis_pendaftaran_id', 20)->nullable()->after('description');
            }

            if (! Schema::hasColumn('admission_paths', 'jenis_pendaftaran_name')) {
                $table->string('jenis_pendaftaran_name')->nullable()->after('jenis_pendaftaran_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admission_paths', function (Blueprint $table): void {
            foreach (['sevima_id', 'jenis_pendaftaran_id', 'jenis_pendaftaran_name'] as $column) {
                if (Schema::hasColumn('admission_paths', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
