<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth', function (Blueprint $table) {
            $table->unsignedTinyInteger('failed_login_attempts')
                ->default(0)
                ->after('status');

            $table->timestamp('locked_until')
                ->nullable()
                ->after('failed_login_attempts');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('locked_until');

            $table->ipAddress('last_login_ip')
                ->nullable()
                ->after('last_login_at');

            $table->text('last_login_user_agent')
                ->nullable()
                ->after('last_login_ip');
        });

        /*
        |--------------------------------------------------------------------------
        | Add inactive status
        |--------------------------------------------------------------------------
        |
        | This assumes your existing status column is ENUM:
        | active / banned
        |
        */

        DB::statement("
            ALTER TABLE auth
            MODIFY status ENUM('active', 'banned', 'inactive')
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Convert inactive users back to active before removing enum value
        |--------------------------------------------------------------------------
        */

        DB::table('auth')
            ->where('status', 'inactive')
            ->update(['status' => 'active']);

        DB::statement("
            ALTER TABLE auth
            MODIFY status ENUM('active', 'banned')
            NOT NULL DEFAULT 'active'
        ");

        Schema::table('auth', function (Blueprint $table) {
            $table->dropColumn([
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
            ]);
        });
    }
};
