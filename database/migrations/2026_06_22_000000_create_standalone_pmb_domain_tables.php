<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campuses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->text('address')->nullable();
            $table->string('maps_url')->nullable();
            $table->boolean('is_main')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('campus_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('label')->nullable();
            $table->string('value');
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faculties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('study_programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('level', 20);
            $table->string('name');
            $table->string('degree')->nullable();
            $table->string('accreditation')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('campus_study_programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_open')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['campus_id', 'study_program_id']);
        });

        Schema::create('class_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('schedule_label')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('pmb_admission_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('academic_year');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('brochure_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('pmb_waves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('pmb_admission_periods')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['admission_period_id', 'code']);
        });

        Schema::create('admission_paths', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('registration_fee')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('pmb_registration_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admission_period_id')->constrained('pmb_admission_periods')->cascadeOnDelete();
            $table->foreignId('wave_id')->nullable()->constrained('pmb_waves')->nullOnDelete();
            $table->foreignId('campus_study_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_path_id')->constrained('admission_paths')->cascadeOnDelete();
            $table->foreignId('class_type_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['admission_period_id', 'is_active']);
        });

        Schema::create('tuition_fee_schemes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_option_id')->constrained('pmb_registration_options')->cascadeOnDelete();
            $table->unsignedInteger('registration_fee')->default(0);
            $table->unsignedTinyInteger('installment_count')->default(1);
            $table->unsignedInteger('installment_amount')->default(0);
            $table->unsignedInteger('semester_fee')->default(0);
            $table->unsignedInteger('total_first_payment')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('scholarships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['institution_id', 'code']);
        });

        Schema::create('pmb_content_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->json('items')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['institution_id', 'category', 'is_active']);
        });

        Schema::create('pmb_faqs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->nullable();
            $table->text('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_faqs');
        Schema::dropIfExists('pmb_content_blocks');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('tuition_fee_schemes');
        Schema::dropIfExists('pmb_registration_options');
        Schema::dropIfExists('admission_paths');
        Schema::dropIfExists('pmb_waves');
        Schema::dropIfExists('pmb_admission_periods');
        Schema::dropIfExists('class_types');
        Schema::dropIfExists('campus_study_programs');
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('faculties');
        Schema::dropIfExists('campus_contacts');
        Schema::dropIfExists('campuses');
        Schema::dropIfExists('institutions');
    }
};
