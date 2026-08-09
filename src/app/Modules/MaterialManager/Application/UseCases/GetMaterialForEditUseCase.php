<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;

final readonly class GetMaterialForEditUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(int $id): Material
    {
        $material = $this->repository->findForEdit($id);

        if (!$material) {
            throw new \DomainException('Material not found');
        }

        return $material;
    }
}
