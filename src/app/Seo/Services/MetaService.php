<?php

namespace App\Seo\Services;

use App\Seo\Contracts\SeoProviderInterface;
use App\Seo\DTO\MetaData;
use Illuminate\Support\Collection;

class MetaService
{
    private Collection $providers;

    public function __construct()
    {
        $this->providers = collect();
    }

    public function registerProvider(SeoProviderInterface $provider): self
    {
        $this->providers->push($provider);
        return $this;
    }

    public function for($entity): MetaData
    {
        $provider = $this->providers->first(fn($p) => $p->supports($entity));

        if (!$provider) {
            return $this->getDefaultMeta($entity);
        }

        return $provider->generate($entity);
    }

    private function getDefaultMeta($entity): MetaData
    {
        $title = is_object($entity) && isset($entity->title)
            ? $entity->title
            : config('app.name');

        return new MetaData(
            title: $title . ' | ' . config('app.name'),
            description: config('app.description') ?? '',
        );
    }
}
