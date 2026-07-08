<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_conversations')) {
            return;
        }

        if (Schema::hasColumn('ai_chat_conversations', 'lead_status')) {
            DB::table('ai_chat_conversations')
                ->where('lead_status', 'new')
                ->update(['lead_status' => 'cold']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_chat_conversations')) {
            return;
        }

        DB::table('ai_chat_conversations')
            ->where('lead_status', 'cold')
            ->update(['lead_status' => 'new']);
    }
};
