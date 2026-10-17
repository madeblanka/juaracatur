<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('player', 'admin', 'vendor') NOT NULL DEFAULT 'player'");

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('player', 'admin') NOT NULL DEFAULT 'player'");
    }
};
