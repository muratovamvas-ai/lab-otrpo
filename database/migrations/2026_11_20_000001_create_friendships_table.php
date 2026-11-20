<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Связь «многие-ко-многим» между пользователями (дружба).
     * Строка user_id -> friend_id означает «user_id добавил friend_id в друзья».
     */
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'friend_id']); // одну дружбу нельзя добавить дважды
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
