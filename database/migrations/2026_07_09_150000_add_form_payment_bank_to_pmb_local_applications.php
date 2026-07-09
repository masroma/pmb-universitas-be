<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            if (! Schema::hasColumn('pmb_local_applications', 'form_payment_bank')) {
                $table->string('form_payment_bank', 20)->nullable()->after('form_payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pmb_local_applications', function (Blueprint $table): void {
            if (Schema::hasColumn('pmb_local_applications', 'form_payment_bank')) {
                $table->dropColumn('form_payment_bank');
            }
        });
    }
};

