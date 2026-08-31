<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\CreateMenuTypeData;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CreateMenuTypeUseCase
{
    public function __construct(
        private MenuTypeRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(CreateMenuTypeData $data): MenuTypeModel
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction(function () use ($data) {
                    $alias = $data->alias ?? $this->slugGenerator->generate($data->title);
                    $alias = $this->ensureUniqueAlias($alias);

                    return $this->repository->create([
                        'title' => $data->title,
                        'alias' => $alias,
                        'description' => $data->description,
                        'ordering' => $data->ordering,
                        'status' => $data->status,
                    ]);
                });
            } catch (UniqueConstraintViolationException $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                usleep(50000);
            }
        }

        throw new \RuntimeException('Failed to create menu type after multiple attempts');
    }

    private function ensureUniqueAlias(string $alias): string
    {
        $originalAlias = $alias;
        $counter = 1;

        while ($this->repository->findByAliasWithLock($alias)) {
            $alias = $originalAlias . '-' . $counter++;
        }

        return $alias;
    }
}
