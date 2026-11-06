<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class GetMaterialForEditUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Material
    {
        $material = $this->repository->findForEdit($id);

        if (! $material) {
            // ModelNotFoundException Laravel отрендерит как 404;
            // DomainException уходил в 500.
            throw (new ModelNotFoundException())->setModel(Material::class, [$id]);
        }

        return $material;
    }
}
