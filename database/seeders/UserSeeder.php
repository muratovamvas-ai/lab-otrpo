<?php

namespace Database\Seeders;

use App\Models\MessierObject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Создаёт трёх демо-пользователей (пароль у всех: password)
 * и распределяет между ними ранее добавленные объекты.
 *
 *   admin@example.com  — администратор (is_admin = true)
 *   ivan@example.com   — обычный пользователь
 *   maria@example.com  — обычный пользователь
 *
 * Запуск отдельно: php artisan db:seed --class=UserSeeder
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'admin' => ['name' => 'Администратор', 'email' => 'admin@example.com', 'is_admin' => true],
            'ivan' => ['name' => 'Иван Петров', 'email' => 'ivan@example.com', 'is_admin' => false],
            'maria' => ['name' => 'Мария Сидорова', 'email' => 'maria@example.com', 'is_admin' => false],
        ];

        $models = [];
        foreach ($users as $username => $data) {
            $user = User::firstOrNew(['username' => $username]);
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->is_admin = $data['is_admin']; // is_admin не в $fillable, поэтому присваиваем напрямую
            if (! $user->exists) {
                $user->password = Hash::make('password');
            }
            $user->save();

            $models[$username] = $user;
        }

        // Распределяем объекты по обозначению (только те, у кого ещё нет автора)
        $assignments = [
            'M10' => 'ivan',
            'M17' => 'ivan',
            'M27' => 'maria',
            'M33' => 'maria',
            'M42' => 'admin',
        ];

        foreach ($assignments as $name => $username) {
            MessierObject::where('name', $name)
                ->whereNull('user_id')
                ->update(['user_id' => $models[$username]->id]);
        }
    }
}
