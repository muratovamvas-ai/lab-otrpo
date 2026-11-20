<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Вспомогательная сущность — комментарии к объектам каталога.
     * Комментарий не существует без объекта и без автора-пользователя.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('messier_object_id')->constrained()->cascadeOnDelete(); // к какому объекту
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();           // кто написал
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
