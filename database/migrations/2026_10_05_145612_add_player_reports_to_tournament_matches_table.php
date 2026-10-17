<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->unsignedBigInteger('player1_reported_winner_id')->nullable()->after('player2_id');
            $table->unsignedBigInteger('player2_reported_winner_id')->nullable()->after('player1_reported_winner_id');
        });

        DB::statement("ALTER TABLE tournament_matches MODIFY status ENUM('pending', 'completed', 'disputed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tournament_matches SET status = 'pending' WHERE status = 'disputed'");
        DB::statement("ALTER TABLE tournament_matches MODIFY status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending'");

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropColumn(['player1_reported_winner_id', 'player2_reported_winner_id']);
        });
    }
};
