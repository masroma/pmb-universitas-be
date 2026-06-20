<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('admin')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('api_token', 64)->nullable()->after('remember_token');
            $table->index('api_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['api_token']);
            $table->dropColumn(['role', 'phone', 'api_token']);
        });
    }
};
