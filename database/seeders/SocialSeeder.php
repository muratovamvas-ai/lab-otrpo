<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Friendship;
use App\Models\MessierObject;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Демо-данные для ЛР5: комментарии и дружба между демо-пользователями.
 * Запуск отдельно: php artisan db:seed --class=SocialSeeder
 * (сначала должны быть выполнены MessierObjectSeeder и UserSeeder)
 */
class SocialSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('username', ['admin', 'ivan', 'maria'])->get()->keyBy('username');

        if ($users->count() < 3) {
            $this->command?->warn('Демо-пользователи не найдены. Сначала выполните: php artisan db:seed');

            return;
        }

        // Дружба ivan <-> maria. Достаточно создать одну сторону:
        // обратную связь создаёт событие «обоюдной дружбы» в модели Friendship.
        Friendship::firstOrCreate([
            'user_id' => $users['ivan']->id,
            'friend_id' => $users['maria']->id,
        ]);

        // Комментарии: [обозначение объекта, автор, текст]
        $comments = [
            ['M10', 'maria', 'Красивое скопление! Его хорошо видно даже в бинокль.'],
            ['M10', 'admin', 'Добавлено в избранное каталога.'],
            ['M27', 'ivan', 'Отличный объект — туманность Гантель очень узнаваема.'],
            ['M27', 'ivan', 'Планирую наблюдать её этим летом в телескоп.'],
            ['M33', 'admin', 'Одна из самых далёких галактик, видимых невооружённым глазом.'],
            ['M42', 'maria', 'Туманность Ориона — мой любимый объект зимнего неба.'],
            ['M42', 'ivan', 'Согласен, особенно хороша Трапеция в центре.'],
        ];

        foreach ($comments as [$objectName, $username, $body]) {
            $object = MessierObject::where('name', $objectName)->first();

            if (! $object) {
                continue;
            }

            Comment::firstOrCreate([
                'messier_object_id' => $object->id,
                'user_id' => $users[$username]->id,
                'body' => $body,
            ]);
        }
    }
}
