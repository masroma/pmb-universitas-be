<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_conversations')) {
            return;
        }

        Schema::table('ai_chat_conversations', function (Blueprint $table): void {
            if (! Schema::hasColumn('ai_chat_conversations', 'visitor_name')) {
                $table->string('visitor_name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'visitor_email')) {
                $table->string('visitor_email')->nullable()->after('visitor_name');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'visitor_whatsapp')) {
                $table->string('visitor_whatsapp', 30)->nullable()->after('visitor_email');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'lead_status')) {
                $table->string('lead_status', 30)->default('new')->after('visitor_whatsapp');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'lead_score')) {
                $table->unsignedSmallInteger('lead_score')->default(0)->after('lead_status');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'lead_interest')) {
                $table->string('lead_interest')->nullable()->after('lead_score');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'lead_qualified_at')) {
                $table->timestamp('lead_qualified_at')->nullable()->after('lead_interest');
            }

            if (! Schema::hasColumn('ai_chat_conversations', 'contact_consent_at')) {
                $table->timestamp('contact_consent_at')->nullable()->after('lead_qualified_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_chat_conversations')) {
            return;
        }

        Schema::table('ai_chat_conversations', function (Blueprint $table): void {
            foreach ([
                'contact_consent_at',
                'lead_qualified_at',
                'lead_interest',
                'lead_score',
                'lead_status',
                'visitor_whatsapp',
                'visitor_email',
                'visitor_name',
            ] as $column) {
                if (Schema::hasColumn('ai_chat_conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
