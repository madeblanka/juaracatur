<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dateTime('starts_at')->nullable()->after('format');
            $table->unsignedInteger('round_gap_minutes')->nullable()->after('starts_at');
        });

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dateTime('scheduled_at')->nullable()->after('round');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'round_gap_minutes']);
        });

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
        });
    }
};
