<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_benefits', function (Blueprint $table): void {
            $table->id();
            $table->string('icon', 10);
            $table->string('title');
            $table->string('emphasis')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_italic')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pmb_study_programs', function (Blueprint $table): void {
            $table->id();
            $table->string('sevima_id')->nullable()->unique();
            $table->string('level')->nullable();
            $table->string('title');
            $table->string('accreditation')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('pmb_registration_paths', function (Blueprint $table): void {
            $table->id();
            $table->string('sevima_id')->nullable()->unique();
            $table->string('title');
            $table->string('period')->nullable();
            $table->string('fee')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('pmb_registration_flows', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('accent_class')->default('bg-blue-700');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pmb_registration_flow_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_flow_id')
                ->constrained('pmb_registration_flows')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_registration_flow_steps');
        Schema::dropIfExists('pmb_registration_flows');
        Schema::dropIfExists('pmb_registration_paths');
        Schema::dropIfExists('pmb_study_programs');
        Schema::dropIfExists('pmb_benefits');
    }
};
