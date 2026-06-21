<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tuition_fees')) {
            return;
        }

        Schema::table('tuition_fees', function (Blueprint $table): void {
            if (! Schema::hasColumn('tuition_fees', 'pmb_period_id')) {
                $table->foreignId('pmb_period_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('pmb_periods')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('tuition_fees', 'pmb_study_program_id')) {
                $table->foreignId('pmb_study_program_id')
                    ->nullable()
                    ->after('pmb_period_id')
                    ->constrained('pmb_study_programs')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tuition_fees')) {
            return;
        }

        Schema::table('tuition_fees', function (Blueprint $table): void {
            if (Schema::hasColumn('tuition_fees', 'pmb_study_program_id')) {
                $table->dropConstrainedForeignId('pmb_study_program_id');
            }

            if (Schema::hasColumn('tuition_fees', 'pmb_period_id')) {
                $table->dropConstrainedForeignId('pmb_period_id');
            }
        });
    }
};
