<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pmb_applicants')) {
            return;
        }

        Schema::create('pmb_applicants', function (Blueprint $table): void {
            $table->id();
            $table->string('sevima_id')->unique();
            $table->string('registration_period_id')->nullable();
            $table->string('registration_period_name')->nullable();
            $table->string('academic_period_id')->nullable();
            $table->string('wave_id')->nullable();
            $table->string('study_system_id')->nullable();
            $table->string('study_system')->nullable();
            $table->string('registration_path_id')->nullable();
            $table->string('registration_path')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->string('code')->nullable();
            $table->string('nim')->nullable();
            $table->string('name');
            $table->string('gender', 5)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('nik')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->boolean('is_re_registered')->default(false);
            $table->timestamp('re_registered_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('academic_period_id');
            $table->index('registration_period_id');
            $table->index('is_re_registered');
            $table->index('is_active');
            $table->index('is_deleted');
            $table->index('nim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_applicants');
    }
};
