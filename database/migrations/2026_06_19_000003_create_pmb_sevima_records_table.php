<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_sevima_records', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type');
            $table->string('sevima_id')->nullable();
            $table->string('parent_type')->nullable();
            $table->string('parent_sevima_id')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->string('period')->nullable();
            $table->string('amount')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'sevima_id']);
            $table->index(['parent_type', 'parent_sevima_id']);
            $table->index(['entity_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_sevima_records');
    }
};
