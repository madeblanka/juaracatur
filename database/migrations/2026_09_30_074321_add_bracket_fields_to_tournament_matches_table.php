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
            $table->foreignId('next_match_id')->nullable()->after('winner_id')
                ->constrained('tournament_matches')->nullOnDelete();
            $table->unsignedTinyInteger('next_match_slot')->nullable()->after('next_match_id');
        });

        // player1/player2 must become nullable to support single-elimination
        // placeholder matches whose participants are only known once an earlier
        // round finishes. doctrine/dbal isn't installed, so use raw SQL instead
        // of Blueprint::change().
        DB::statement('ALTER TABLE tournament_matches MODIFY player1_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE tournament_matches MODIFY player2_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('next_match_id');
            $table->dropColumn('next_match_slot');
        });

        DB::statement('ALTER TABLE tournament_matches MODIFY player1_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE tournament_matches MODIFY player2_id BIGINT UNSIGNED NOT NULL');
    }
};
