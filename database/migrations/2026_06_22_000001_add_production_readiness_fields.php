<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('admin_pmb')->after('password');
            }
        });

        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            foreach ([
                'pmb_admission_period_id',
                'pmb_wave_id',
                'pmb_registration_option_id',
                'campus_id',
                'standalone_study_program_id',
                'admission_path_id',
                'class_type_id',
            ] as $column) {
                if (! Schema::hasColumn('pmb_local_applications', $column)) {
                    $table->unsignedBigInteger($column)->nullable()->after('program_option_id');
                }
            }

            if (! Schema::hasColumn('pmb_local_applications', 'campus_name')) {
                $table->string('campus_name')->nullable()->after('registration_period_name');
            }

            if (! Schema::hasColumn('pmb_local_applications', 'registration_snapshot')) {
                $table->json('registration_snapshot')->nullable()->after('applicant_note');
            }
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('action', 80);
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            foreach ([
                'pmb_admission_period_id',
                'pmb_wave_id',
                'pmb_registration_option_id',
                'campus_id',
                'standalone_study_program_id',
                'admission_path_id',
                'class_type_id',
                'campus_name',
                'registration_snapshot',
            ] as $column) {
                if (Schema::hasColumn('pmb_local_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
