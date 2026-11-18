<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Сохраняет картинку в storage/app/public/objects и подгоняет её
 * под параметры вёрстки (карточка 16:10 → 960×600, обрезка по центру).
 * Использует расширение GD (в PHP оно есть из коробки; в php.ini: extension=gd).
 */
class ImageResizer
{
    public const WIDTH = 960;
    public const HEIGHT = 600;
    public const DIRECTORY = 'objects';

    /** Сохранить файл из формы. Возвращает путь относительно диска public. */
    public function storeUploaded(UploadedFile $file): string
    {
        return $this->storeBinary($file->get());
    }

    /** Сохранить картинку из бинарной строки (используется и сидером). */
    public function storeBinary(string $binary): string
    {
        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            throw new RuntimeException('Не удалось прочитать изображение.');
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        // «cover»: масштабируем так, чтобы заполнить 960×600, лишнее обрезаем по центру
        $scale = max(self::WIDTH / $srcW, self::HEIGHT / $srcH);
        $cropW = (int) round(self::WIDTH / $scale);
        $cropH = (int) round(self::HEIGHT / $scale);
        $cropX = (int) floor(($srcW - $cropW) / 2);
        $cropY = (int) floor(($srcH - $cropH) / 2);

        $target = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        // белый фон на случай прозрачного PNG
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, $cropX, $cropY, self::WIDTH, self::HEIGHT, $cropW, $cropH);

        ob_start();
        imagejpeg($target, null, 85);
        $jpeg = ob_get_clean();

        $path = self::DIRECTORY.'/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
