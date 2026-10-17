<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('player', 'admin', 'vendor', 'venue') NOT NULL DEFAULT 'player'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('player', 'admin', 'vendor') NOT NULL DEFAULT 'player'");
    }
};
