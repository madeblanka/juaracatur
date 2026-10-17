<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('exchange_item_id')->constrained('exchange_items')->restrictOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('point_cost');
            $table->enum('status', ['menunggu', 'selesai'])->default('menunggu');
            $table->string('voucher_code', 32)->nullable()->unique();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
