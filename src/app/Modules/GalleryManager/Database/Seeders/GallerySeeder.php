<?php

namespace App\Modules\GalleryManager\Database\Seeders;

use App\Modules\GalleryManager\Infrastructure\Models\GalleryImageModel;
use App\Modules\GalleryManager\Infrastructure\Models\GalleryModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Демо-галереи для публичных страниц /galleries/{id}.
 *
 * Детерминированный и идемпотентный: галереи — firstOrCreate по title,
 * изображения — firstOrCreate по (gallery_id, image_path). Файлы-плейсхолдеры
 * генерируются программно (GD) в storage/app/public и не перезаписываются.
 */
class GallerySeeder extends Seeder
{
    private const GALLERIES = [
        [
            'title' => 'Офис компании',
            'type' => 'grid',
            'ordering' => 1,
            'images' => ['Рабочая зона', 'Переговорная', 'Ресепшн'],
            'color' => [59, 130, 246],
        ],
        [
            'title' => 'Производство',
            'type' => 'slideshow',
            'ordering' => 2,
            'images' => ['Цех № 1', 'Линия сборки', 'Склад'],
            'color' => [34, 197, 94],
        ],
        [
            'title' => 'Команда',
            'type' => 'slider',
            'ordering' => 3,
            'images' => ['Отдел разработки', 'Бухгалтерия', 'Корпоратив'],
            'color' => [249, 115, 22],
        ],
    ];

    public function run(): void
    {
        foreach (self::GALLERIES as $galleryData) {
            $gallery = GalleryModel::firstOrCreate(
                ['title' => $galleryData['title']],
                [
                    'type' => $galleryData['type'],
                    'settings' => [],
                    'status' => true,
                    'ordering' => $galleryData['ordering'],
                ]
            );

            $directory = 'galleries/'.$gallery->id;

            foreach ($galleryData['images'] as $i => $imageTitle) {
                $path = $directory.'/photo-'.($i + 1).'.png';

                $this->ensurePlaceholderImage($path, $galleryData['color'], $imageTitle);

                GalleryImageModel::firstOrCreate(
                    ['gallery_id' => $gallery->id, 'image_path' => $path],
                    [
                        'title' => $imageTitle,
                        'alt_text' => $galleryData['title'].' — '.$imageTitle,
                        'ordering' => $i + 1,
                        'status' => true,
                    ]
                );
            }
        }
    }

    /**
     * Генерирует PNG-плейсхолдер 1200×800, если файла ещё нет.
     * (GD в окружении собран без JPEG — используем PNG.)
     *
     * @param  array{0: int, 1: int, 2: int}  $color
     */
    private function ensurePlaceholderImage(string $relativePath, array $color, string $label): void
    {
        $absolutePath = Storage::disk('public')->path($relativePath);

        if (File::exists($absolutePath)) {
            return;
        }

        File::ensureDirectoryExists(dirname($absolutePath));

        $image = imagecreatetruecolor(1200, 800);
        $background = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        imagefilledrectangle($image, 0, 0, 1200, 800, $background);

        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 40, 380, $label, $textColor);

        imagepng($image, $absolutePath);
        imagedestroy($image);
    }
}
