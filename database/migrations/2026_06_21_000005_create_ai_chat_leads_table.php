<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_chat_leads')) {
            return;
        }

        Schema::create('ai_chat_leads', function (Blueprint $table): void {
            $table->id();
            $table->string('ai_chat_conversation_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('study_program_interest')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('status', 30)->default('qualified');
            $table->json('qualification')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamps();

            $table
                ->foreign('ai_chat_conversation_id', 'ai_chat_leads_conversation_fk')
                ->references('id')
                ->on('ai_chat_conversations')
                ->cascadeOnDelete();
            $table->unique('ai_chat_conversation_id', 'ai_chat_leads_conversation_unique');
            $table->index(['status', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_leads');
    }
};
