<?php

namespace App\Modules\MaterialManager\Application\Services;

use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\SettingManager\Application\Services\SettingService;

/**
 * Единая точка управления главной страницей.
 *
 * Источник истины — настройки homepage_type/homepage_material_id (Батч 4).
 * Флаг show_on_homepage материалов — производный индикатор для отображения
 * (колонка «На главной» в списке), синхронизируется здесь же и сам по себе
 * главную не определяет.
 */
class HomepageManager
{
    public const TYPE_MATERIAL = 'material';

    public const TYPE_DEFAULT = 'default';

    public function __construct(
        private SettingService $settings,
        private MaterialRepositoryInterface $repository,
    ) {}

    public function currentMaterialId(): ?int
    {
        $settings = $this->settings->getAllSettings();

        if (($settings['homepage_type'] ?? null) !== self::TYPE_MATERIAL) {
            return null;
        }

        $id = (int) ($settings['homepage_material_id'] ?? 0);

        return $id > 0 ? $id : null;
    }

    public function isHomepage(int $materialId): bool
    {
        return $this->currentMaterialId() === $materialId;
    }

    public function assign(int $materialId): void
    {
        $this->settings->updateSettings([
            'homepage_type' => self::TYPE_MATERIAL,
            'homepage_material_id' => $materialId,
        ]);

        $this->repository->syncHomepageFlag($materialId);
    }

    /**
     * Снимает главность. Конкретный материал снимается только если он
     * сейчас главный; без аргумента главность снимается всегда.
     */
    public function unassign(?int $materialId = null): void
    {
        if ($materialId !== null && ! $this->isHomepage($materialId)) {
            return;
        }

        $this->settings->updateSettings([
            'homepage_type' => self::TYPE_DEFAULT,
        ]);

        $this->repository->syncHomepageFlag(null);
    }

    /**
     * Пересинхронизирует флаги материалов с текущими настройками.
     * Вызывается после ручного изменения настроек главной страницы.
     */
    public function syncFlags(): void
    {
        $this->repository->syncHomepageFlag($this->currentMaterialId());
    }
}
