<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth', function (Blueprint $table) {
            $table->string('status')->default('active')->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('auth', function (Blueprint $table) {
            $table->dropColumn(['status', 'email_verified_at']);
        });
    }
};
