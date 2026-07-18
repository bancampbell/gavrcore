<?php

namespace App\Seo\Contracts;

use App\Seo\DTO\MetaData;

interface SeoProviderInterface
{
    public function supports($entity): bool;

    public function generate($entity): MetaData;

    public function getType(): string;
}
