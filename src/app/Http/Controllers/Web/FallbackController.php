<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Modules\MaterialManager\Infrastructure\Http\Controllers\WebMaterialController;
use Illuminate\Http\Request;

/**
 * Fallback-контроллер приложения.
 *
 * Делегирует обработку неразобранных односегментных URL модулю MaterialManager.
 * Модуль не знает о существовании /login, /register и т.д. —
 * приложение решает, когда вызывать модульную логику.
 */
final readonly class FallbackController
{
    public function __construct(
        private WebMaterialController $materialController,
    ) {}

    public function __invoke(Request $request, string $slug): mixed
    {
        return $this->materialController->show($slug);
    }
}
