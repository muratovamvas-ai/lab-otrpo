<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MessierObjectSeeder::class, // объекты каталога
            UserSeeder::class,          // пользователи и назначение им объектов
            SocialSeeder::class,        // дружба и комментарии (ЛР5)
        ]);
    }
}
