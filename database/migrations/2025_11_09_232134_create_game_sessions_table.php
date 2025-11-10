<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('game_sessions', function (Blueprint $table) {
        $table->id();
        $table->string('player_name', 64);
        $table->unsignedInteger('kills')->default(0);
        $table->unsignedInteger('rooms')->default(0);
        $table->dateTime('ended_at');     // cuándo terminó esa sesión
        $table->timestamps();             // created_at / updated_at
        $table->index(['player_name','ended_at']);
        $table->index('ended_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
