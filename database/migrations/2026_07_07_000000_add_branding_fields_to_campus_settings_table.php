<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_settings', function (Blueprint $table): void {
            $table->string('pmb_tagline')->nullable()->after('campus_name');
            $table->text('hero_description')->nullable()->after('pmb_tagline');
        });
    }

    public function down(): void
    {
        Schema::table('campus_settings', function (Blueprint $table): void {
            $table->dropColumn(['pmb_tagline', 'hero_description']);
        });
    }
};
