<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\CreateFormData;
use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\Services\SlugGeneratorInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;
use App\Modules\FormBuilder\Domain\ValueObjects\FormSettings;
use App\Modules\FormBuilder\Domain\ValueObjects\FormStatus;
use Illuminate\Database\QueryException;

class CreateFormUseCase
{
    public function __construct(
        protected FormRepositoryInterface $repository,
        protected SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(CreateFormData $data): Form
    {
        $baseAlias = $data->alias ?: $this->slugGenerator->generate($data->title);

        if (trim($baseAlias) === '') {
            $baseAlias = 'form';
        }

        $alias = $data->alias ? $baseAlias : $this->makeUniqueAlias($baseAlias);

        $attempts = 0;
        $maxAttempts = 10;

        while ($attempts < $maxAttempts) {
            try {
                $form = new Form(
                    id: null,
                    title: $data->title,
                    alias: $alias,
                    description: $data->description,
                    fields: new FormFieldCollection($data->fields),
                    settings: new FormSettings($data->settings),
                    notificationEmails: $data->notificationEmails,
                    status: new FormStatus($data->status),
                    isDynamic: $data->isDynamic,
                    submissionsCount: 0,
                );

                return $this->repository->save($form);
            } catch (QueryException $e) {
                // Если alias был передан явно — это не race condition, а валидационная ошибка клиента
                if ($data->alias) {
                    throw $e;
                }

                $message = $e->getMessage();
                if (!str_contains($message, 'forms_alias_unique') && !str_contains($message, 'Duplicate entry')) {
                    throw $e;
                }

                // Race condition: кто-то занял alias между findByAlias и INSERT
                $alias = $this->makeUniqueAlias($baseAlias . '-' . bin2hex(random_bytes(4)));
                $attempts++;
            }
        }

        throw new \RuntimeException('Unable to generate unique alias after ' . $maxAttempts . ' attempts');
    }

    private function makeUniqueAlias(string $base): string
    {
        $alias = $base;
        if ($this->repository->findByAlias($alias) === null) {
            return $alias;
        }

        do {
            $alias = $base . '-' . bin2hex(random_bytes(4));
        } while ($this->repository->findByAlias($alias) !== null);

        return $alias;
    }
}