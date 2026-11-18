<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица объектов каталога Мессье (модель MessierObject).
     * Поля названы в snake_case в нижнем регистре.
     */
    public function up(): void
    {
        Schema::create('messier_objects', function (Blueprint $table) {
            $table->id();
            $table->string('constellation', 100);        // созвездие: «Змееносец»
            $table->string('name', 20);                  // обозначение: «M10»
            $table->string('type', 100);                 // тип: «Шаровое скопление»
            $table->string('short_description', 500);    // краткое описание для карточки
            $table->text('details');                     // подробный текст для show / Modal
            $table->date('catalogued_at')->nullable();   // дата внесения в каталог
            $table->string('image_path')->nullable();    // файл в storage/app/public/objects
            $table->timestamps();
            $table->softDeletes();                       // мягкое удаление (deleted_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messier_objects');
    }
};
