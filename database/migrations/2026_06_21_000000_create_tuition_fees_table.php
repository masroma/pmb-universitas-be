<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_fees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pmb_period_id')->nullable()->constrained('pmb_periods')->nullOnDelete();
            $table->foreignId('pmb_study_program_id')->nullable()->constrained('pmb_study_programs')->nullOnDelete();
            $table->string('program_level')->default('Sarjana');
            $table->string('campus');
            $table->string('wave')->nullable();
            $table->string('study_program')->nullable();
            $table->unsignedInteger('registration_fee')->default(0);
            $table->unsignedTinyInteger('installment_count')->default(6);
            $table->unsignedInteger('installment_amount');
            $table->unsignedInteger('semester_fee');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['program_level', 'campus', 'wave']);
            $table->index(['pmb_period_id', 'pmb_study_program_id']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_fees');
    }
};
