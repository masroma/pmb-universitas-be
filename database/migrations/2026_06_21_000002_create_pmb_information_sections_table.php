<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_information_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('program_level')->nullable();
            $table->string('category');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->json('items')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['program_level', 'category']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_information_sections');
    }
};
