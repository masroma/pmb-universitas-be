<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_conversations')) {
            Schema::create('ai_chat_conversations', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_chat_messages')) {
            Schema::create('ai_chat_messages', function (Blueprint $table): void {
                $table->id();
                $table->string('ai_chat_conversation_id');
                $table->string('role', 20);
                $table->text('content');
                $table->timestamps();

                $table
                    ->foreign('ai_chat_conversation_id', 'ai_chat_messages_conversation_fk')
                    ->references('id')
                    ->on('ai_chat_conversations')
                    ->cascadeOnDelete();
                $table->index(['ai_chat_conversation_id', 'id'], 'ai_chat_messages_conversation_id_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_conversations');
    }
};
