<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_chat_messages')) {
            Schema::table('ai_chat_messages', function (Blueprint $table): void {
                if (! Schema::hasColumn('ai_chat_messages', 'client_message_id')) {
                    $table->string('client_message_id', 100)->nullable()->after('content');
                    $table->unique(
                        ['ai_chat_conversation_id', 'client_message_id'],
                        'ai_chat_messages_conversation_client_unique'
                    );
                }
            });
        }

        if (Schema::hasTable('ai_chat_conversations')) {
            Schema::table('ai_chat_conversations', function (Blueprint $table): void {
                if (! Schema::hasColumn('ai_chat_conversations', 'last_qualification_key')) {
                    $table->string('last_qualification_key', 100)->nullable()->after('contact_consent_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_chat_messages')) {
            Schema::table('ai_chat_messages', function (Blueprint $table): void {
                if (Schema::hasColumn('ai_chat_messages', 'client_message_id')) {
                    $table->dropUnique('ai_chat_messages_conversation_client_unique');
                    $table->dropColumn('client_message_id');
                }
            });
        }

        if (Schema::hasTable('ai_chat_conversations')) {
            Schema::table('ai_chat_conversations', function (Blueprint $table): void {
                if (Schema::hasColumn('ai_chat_conversations', 'last_qualification_key')) {
                    $table->dropColumn('last_qualification_key');
                }
            });
        }
    }
};
