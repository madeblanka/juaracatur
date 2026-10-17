<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->unsignedInteger('entry_points')->default(0)->after('round_gap_minutes');
        });

        Schema::table('tournament_participants', function (Blueprint $table) {
            $table->unsignedInteger('entry_paid')->default(0)->after('draws');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_participants', function (Blueprint $table) {
            $table->dropColumn('entry_paid');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('entry_points');
        });
    }
};
