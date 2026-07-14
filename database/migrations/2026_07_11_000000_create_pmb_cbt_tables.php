<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_cbt_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->default('Tes Seleksi PMB');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->unsignedSmallInteger('questions_per_attempt')->default(10);
            $table->unsignedTinyInteger('pass_score')->default(60);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pmb_cbt_questions', function (Blueprint $table): void {
            $table->id();
            $table->string('category')->default('umum');
            $table->text('question');
            $table->json('options');
            $table->string('correct_option', 1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pmb_cbt_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pmb_local_application_id')->constrained('pmb_local_applications')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('status', 20)->default('in_progress');
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->boolean('passed')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['pmb_local_application_id', 'status']);
        });

        Schema::create('pmb_cbt_attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pmb_cbt_attempt_id')->constrained('pmb_cbt_attempts')->cascadeOnDelete();
            $table->foreignId('pmb_cbt_question_id')->constrained('pmb_cbt_questions')->cascadeOnDelete();
            $table->unsignedSmallInteger('question_order');
            $table->string('selected_option', 1)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();

            $table->unique(['pmb_cbt_attempt_id', 'pmb_cbt_question_id'], 'pmb_cbt_answers_unique');
        });

        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            $table->string('cbt_status', 20)->default('locked')->after('form_payment_note');
            $table->unsignedSmallInteger('cbt_score')->nullable()->after('cbt_status');
            $table->unsignedTinyInteger('cbt_attempt_count')->default(0)->after('cbt_score');
            $table->timestamp('cbt_passed_at')->nullable()->after('cbt_attempt_count');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            $table->dropColumn(['cbt_status', 'cbt_score', 'cbt_attempt_count', 'cbt_passed_at']);
        });

        Schema::dropIfExists('pmb_cbt_attempt_answers');
        Schema::dropIfExists('pmb_cbt_attempts');
        Schema::dropIfExists('pmb_cbt_questions');
        Schema::dropIfExists('pmb_cbt_settings');
    }
};
