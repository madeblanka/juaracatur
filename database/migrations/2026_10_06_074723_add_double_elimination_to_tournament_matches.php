<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tournaments MODIFY format ENUM('round_robin', 'single_elimination', 'double_elimination') NOT NULL DEFAULT 'round_robin'");

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->string('bracket', 10)->default('winners')->after('round');
            $table->unsignedBigInteger('loser_next_match_id')->nullable()->after('next_match_slot');
            $table->unsignedTinyInteger('loser_next_match_slot')->nullable()->after('loser_next_match_id');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropColumn(['bracket', 'loser_next_match_id', 'loser_next_match_slot']);
        });

        DB::statement("UPDATE tournaments SET format = 'single_elimination' WHERE format = 'double_elimination'");
        DB::statement("ALTER TABLE tournaments MODIFY format ENUM('round_robin', 'single_elimination') NOT NULL DEFAULT 'round_robin'");
    }
};
