<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pmb_periods', function (Blueprint $table): void {
            $table->string('brochure_path')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_periods', function (Blueprint $table): void {
            $table->dropColumn('brochure_path');
        });
    }
};
