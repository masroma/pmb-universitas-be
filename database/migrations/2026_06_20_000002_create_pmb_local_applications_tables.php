<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pmb_local_applications')) {
            Schema::create('pmb_local_applications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('status')->default('draft');
                $table->string('academic_period_id')->nullable();
                $table->string('academic_period_name')->nullable();
                $table->string('registration_period_id')->nullable();
                $table->string('registration_period_name')->nullable();
                $table->unsignedBigInteger('program_option_id')->nullable();
                $table->string('study_program_id')->nullable();
                $table->string('study_program_name')->nullable();
                $table->string('registration_path_id')->nullable();
                $table->string('registration_path_name')->nullable();
                $table->string('study_system_id')->nullable();
                $table->string('study_system_name')->nullable();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('gender', 20)->nullable();
                $table->string('birth_place')->nullable();
                $table->date('birth_date')->nullable();
                $table->string('nik')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('province')->nullable();
                $table->string('country')->nullable();
                $table->text('applicant_note')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_note')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('registration_period_id');
                $table->index('academic_period_id');
            });
        }

        if (! Schema::hasTable('pmb_local_application_documents')) {
            Schema::create('pmb_local_application_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('pmb_local_application_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->string('original_name');
                $table->string('path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();

                $table->index(['pmb_local_application_id', 'type'], 'pmb_local_docs_app_type_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_local_application_documents');
        Schema::dropIfExists('pmb_local_applications');
    }
};
