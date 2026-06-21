<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_leads')) {
            return;
        }

        Schema::table('ai_chat_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('ai_chat_leads', 'follow_up_status')) {
                $table->string('follow_up_status', 30)->default('new')->after('status');
            }

            if (! Schema::hasColumn('ai_chat_leads', 'follow_up_note')) {
                $table->text('follow_up_note')->nullable()->after('follow_up_status');
            }

            if (! Schema::hasColumn('ai_chat_leads', 'followed_up_at')) {
                $table->timestamp('followed_up_at')->nullable()->after('follow_up_note');
            }

            if (! Schema::hasColumn('ai_chat_leads', 'followed_up_by')) {
                $table->foreignId('followed_up_by')->nullable()->after('followed_up_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_chat_leads')) {
            return;
        }

        Schema::table('ai_chat_leads', function (Blueprint $table): void {
            if (Schema::hasColumn('ai_chat_leads', 'followed_up_by')) {
                $table->dropConstrainedForeignId('followed_up_by');
            }

            foreach (['followed_up_at', 'follow_up_note', 'follow_up_status'] as $column) {
                if (Schema::hasColumn('ai_chat_leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
