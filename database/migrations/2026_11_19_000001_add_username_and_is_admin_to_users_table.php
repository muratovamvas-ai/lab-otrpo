<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // username используется в адресах: /users/{username}/objects
            $table->string('username', 30)->nullable()->unique();
            // флаг администратора, по умолчанию false
            $table->boolean('is_admin')->default(false);
        });

        // Уже существующим пользователям (если есть) выдаём логин вида user1, user2, ...
        DB::table('users')->whereNull('username')->orderBy('id')->get(['id'])->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['username' => 'user'.$user->id]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'is_admin']);
        });
    }
};
