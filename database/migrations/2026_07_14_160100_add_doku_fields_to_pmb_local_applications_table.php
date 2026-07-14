<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table) {
            $table->string('doku_invoice_number')->nullable()->after('form_payment_note');
            $table->string('doku_request_id')->nullable()->after('doku_invoice_number');
            $table->text('doku_payment_url')->nullable()->after('doku_request_id');
            $table->string('doku_payment_channel')->nullable()->after('doku_payment_url');
            $table->timestamp('doku_paid_at')->nullable()->after('doku_payment_channel');
            $table->json('doku_raw_payload')->nullable()->after('doku_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table) {
            $table->dropColumn([
                'doku_invoice_number',
                'doku_request_id',
                'doku_payment_url',
                'doku_payment_channel',
                'doku_paid_at',
                'doku_raw_payload',
            ]);
        });
    }
};
