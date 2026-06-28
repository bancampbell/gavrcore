<?php

namespace App\Services;

use App\Models\Gallery;

class GalleryParserService
{
    public function parseContent(string $content): string
    {
        $pattern = '/\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/';

        return preg_replace_callback($pattern, function ($matches) {
            $galleryId = $matches[1];
            $gallery = Gallery::with('images')->find($galleryId);

            // Проверяем статус
            if (!$gallery || !$gallery->status) {
                return ''; // Если галерея не найдена или не опубликована — ничего не показываем
            }

            $settings = $gallery->settings ?? [];
            $gutter = $settings['gutter'] ?? 20;
            $alignment = $settings['alignment'] ?? 'left';
            $border = $settings['media']['border'] ?? 'none';
            $showTitle = $settings['content']['show_title'] ?? true;
            $showContent = $settings['content']['show_content'] ?? false;
            $mediaWidth = $settings['media']['width'] ?? 'auto';
            $mediaHeight = $settings['media']['height'] ?? 'auto';

            $borderClass = $border === 'none' ? '' : ($border === 'rounded' ? 'rounded-lg' : 'rounded-full');
            $alignClass = $alignment === 'left' ? 'justify-start' : ($alignment === 'center' ? 'justify-center' : 'justify-end');

            $widthStyle = $mediaWidth !== 'auto' ? 'width: ' . $mediaWidth . 'px;' : '';
            $heightStyle = $mediaHeight !== 'auto' ? 'height: ' . $mediaHeight . 'px;' : '';

            $html = '<div class="gallery-server-renderer" style="margin-bottom: 24px;">';
            $html .= '<div class="flex ' . $alignClass . '">';
            $html .= '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" style="gap: ' . $gutter . 'px;">';

            foreach ($gallery->images as $image) {
                $html .= '<div class="overflow-hidden ' . $borderClass . '">';
                $html .= '<img src="' . $image->image_path . '" alt="' . ($image->alt_text ?? $image->title ?? 'Изображение') . '" style="' . $widthStyle . ' ' . $heightStyle . ' width: 100%; height: auto; object-fit: cover; transition: transform 0.3s;" class="hover:scale-105">';
                if ($showTitle && $image->title) {
                    $html .= '<div class="mt-2 text-sm font-medium text-gray-800">' . $image->title . '</div>';
                }
                if ($showContent && $image->description) {
                    $html .= '<div class="mt-1 text-sm text-gray-600">' . $image->description . '</div>';
                }
                $html .= '</div>';
            }

            $html .= '</div></div></div>';

            return $html;
        }, $content);
    }
}
