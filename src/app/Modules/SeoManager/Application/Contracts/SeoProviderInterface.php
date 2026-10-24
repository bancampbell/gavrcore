<?php

namespace App\Modules\SeoManager\Application\Contracts;

use App\Modules\SeoManager\Application\DTO\MetaData;

interface SeoProviderInterface
{
    public function supports($entity): bool;

    public function generate($entity): MetaData;

    public function getType(): string;
}
