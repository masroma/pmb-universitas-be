<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pmb_periods')) {
            return;
        }

        Schema::create('pmb_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('sevima_id')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('academic_year')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->date('midterm_starts_at')->nullable();
            $table->date('midterm_ends_at')->nullable();
            $table->date('final_starts_at')->nullable();
            $table->date('final_ends_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('academic_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_periods');
    }
};
