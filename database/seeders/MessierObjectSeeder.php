<?php

namespace Database\Seeders;

use App\Models\MessierObject;
use App\Services\ImageResizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Заполняет таблицу 5 объектами из лабораторной работы №2.
 * Картинки скачиваются с Wikimedia, уменьшаются и сохраняются в
 * storage/app/public/objects. Если интернета нет — объект создаётся без
 * картинки (будет заглушка); повторный запуск seed докачает недостающие.
 *
 * В тексте details подсказки задаются как [слово|пояснение].
 */
class MessierObjectSeeder extends Seeder
{
    public function run(ImageResizer $images): void
    {
        foreach ($this->objects() as $item) {
            $imageUrl = $item['image_url'];
            unset($item['image_url']);

            // updateOrCreate — чтобы повторный запуск не плодил дубликаты
            $object = MessierObject::withTrashed()->updateOrCreate(
                ['name' => $item['name']],
                $item
            );

            if ($object->trashed()) {
                $object->restore();
            }

            if (! $object->image_path) {
                try {
                    $response = Http::withHeaders([
                        'User-Agent' => 'MessierCatalogLab3/1.0 (student project; Laravel)',
                    ])->timeout(60)->get($imageUrl);

                    if ($response->successful()) {
                        $object->image_path = $images->storeBinary($response->body());
                        $object->save();
                        $this->command?->info("Картинка для {$object->name} сохранена.");
                    } else {
                        $this->command?->warn("Картинка для {$object->name}: HTTP {$response->status()}.");
                    }
                } catch (Throwable $e) {
                    $this->command?->warn("Картинка для {$object->name} не загружена: {$e->getMessage()}");
                }
            }
        }
    }

    private function objects(): array
    {
        $thumb = 'https://upload.wikimedia.org/wikipedia/commons/thumb/';

        return [
            [
                'constellation' => 'Змееносец',
                'name' => 'M10',
                'type' => 'Шаровое скопление',
                'short_description' => 'Змеено́сец (лат. Ophiuchus) — очень большое и тусклое экваториальное созвездие.',
                'catalogued_at' => '1764-05-29',
                'image_url' => $thumb.'6/67/Messier_10_Hubble_WikiSky.jpg/960px-Messier_10_Hubble_WikiSky.jpg',
                'details' => <<<'TXT'
В созвездии Змееносца находится M10 — яркое [шаровое скопление|Плотный шар из сотен тысяч старых звёзд, связанных гравитацией.]. Шарль Мессье открыл его в 1764 году.

До скопления около 14 тысяч [световых лет|Расстояние, которое свет проходит за год: примерно 9,46 трлн км.], а его поперечник — около 80 световых лет. Его [звёздная величина|Мера яркости объекта на небе. Чем меньше число, тем объект ярче.] 6,6, поэтому M10 хорошо виден даже в бинокль.
TXT,
            ],
            [
                'constellation' => 'Стрелец',
                'name' => 'M17',
                'type' => 'Эмиссионная туманность',
                'short_description' => 'Стреле́ц (лат. Sagittarius, Sgr) — зодиакальное созвездие, лежащее между Козерогом и Скорпионом.',
                'catalogued_at' => '1764-06-03',
                'image_url' => $thumb.'7/7f/VST_image_of_the_spectacular_star-forming_region_Messier_17_%28Omega_Nebula%29.jpg/960px-VST_image_of_the_spectacular_star-forming_region_Messier_17_%28Omega_Nebula%29.jpg',
                'details' => <<<'TXT'
M17, или туманность Омега, — облако газа в Стрельце, где прямо сейчас рождаются новые звёзды. Её также называют туманностью Лебедь.

Это [эмиссионная туманность|Облако газа, которое светится само: его нагревают излучением соседние горячие звёзды.] на расстоянии около 5 500 световых лет. Её открыл [де Шезо|Швейцарский астроном XVIII века, открывший несколько туманностей до Мессье.] в 1745 году, а Мессье внёс в каталог в 1764-м.
TXT,
            ],
            [
                'constellation' => 'Лисичка',
                'name' => 'M27',
                'type' => 'Планетарная туманность',
                'short_description' => 'Лиси́чка (лат. Vulpecula, Vul) — тусклое созвездие северного полушария, находящееся внутри Летнего треугольника.',
                'catalogued_at' => '1764-07-12',
                'image_url' => $thumb.'3/3a/Messier27.jpg/960px-Messier27.jpg',
                'details' => <<<'TXT'
M27, туманность Гантель, — первая открытая [планетарная туманность|Оболочка газа, которую сбросила умирающая звезда. К планетам отношения не имеет — просто в старые телескопы была похожа на диск планеты.] (Мессье, 1764 год).

В её центре находится [белый карлик|Остывающее ядро звезды размером примерно с Землю.] — остаток звезды. До туманности примерно 1 360 световых лет, а её возраст оценивают в несколько тысяч лет.
TXT,
            ],
            [
                'constellation' => 'Треугольник',
                'name' => 'M33',
                'type' => 'Галактика',
                'short_description' => 'Треуго́льник (лат. Triangulum, Tri) — созвездие северного полушария неба. Занимает на небе площадь 131,8 квадратного градуса.',
                'catalogued_at' => '1764-08-25',
                'image_url' => $thumb.'2/2c/M33.jpg/960px-M33.jpg',
                'details' => <<<'TXT'
M33 — спиральная галактика, третья по размеру в [Местной группе|Группа из нескольких десятков галактик, куда входят Млечный Путь и Андромеда.] после Андромеды и Млечного Пути.

До неё около 2,7 миллиона световых лет. В очень тёмную ночь её можно увидеть даже без телескопа — это один из самых далёких объектов, видимых [невооружённым глазом|То есть без бинокля и телескопа.].
TXT,
            ],
            [
                'constellation' => 'Орион',
                'name' => 'M42',
                'type' => 'Эмиссионная туманность',
                'short_description' => 'Орио́н (греч. Ὠρίων) — созвездие в области небесного экватора. Это одно из самых заметных и узнаваемых созвездий на ночном небе.',
                'catalogued_at' => '1769-03-04',
                'image_url' => $thumb.'f/f3/Orion_Nebula_-_Hubble_2006_mosaic_18000.jpg/960px-Orion_Nebula_-_Hubble_2006_mosaic_18000.jpg',
                'details' => <<<'TXT'
M42 — самая яркая туманность на ночном небе, её видно в «мече» Ориона простым глазом. Это ближайшая к нам крупная [область звездообразования|Место, где из газа и пыли сжимаются и рождаются новые звёзды.].

Расстояние — около 1 340 световых лет. Туманность освещают звёзды молодого скопления [Трапеция|Четыре яркие молодые звезды в центре туманности, расположенные трапецией.].
TXT,
            ],
        ];
    }
}
