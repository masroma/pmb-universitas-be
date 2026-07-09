<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            if (! Schema::hasColumn('pmb_local_applications', 'form_payment_status')) {
                $table->string('form_payment_status')->default('pending')->after('status');
            }

            if (! Schema::hasColumn('pmb_local_applications', 'form_payment_amount')) {
                $table->unsignedInteger('form_payment_amount')->default(0)->after('form_payment_status');
            }

            if (! Schema::hasColumn('pmb_local_applications', 'form_paid_at')) {
                $table->timestamp('form_paid_at')->nullable()->after('form_payment_amount');
            }

            if (! Schema::hasColumn('pmb_local_applications', 'form_paid_by')) {
                $table->foreignId('form_paid_by')->nullable()->after('form_paid_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('pmb_local_applications', 'form_payment_note')) {
                $table->text('form_payment_note')->nullable()->after('form_paid_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            if (Schema::hasColumn('pmb_local_applications', 'form_paid_by')) {
                $table->dropConstrainedForeignId('form_paid_by');
            }

            $table->dropColumn([
                'form_payment_status',
                'form_payment_amount',
                'form_paid_at',
                'form_payment_note',
            ]);
        });
    }
};
