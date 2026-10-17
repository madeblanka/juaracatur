<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('challenger_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opponent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('venue_id')->constrained('venues')->restrictOnDelete();
            $table->unsignedInteger('wager_points');
            $table->enum('status', ['open', 'accepted', 'completed', 'cancelled', 'disputed'])->default('open');
            $table->dateTime('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('challenger_reported_winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('opponent_reported_winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
