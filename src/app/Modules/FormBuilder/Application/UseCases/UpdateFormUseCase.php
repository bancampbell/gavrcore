<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\UpdateFormData;
use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\Services\SlugGeneratorInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;
use App\Modules\FormBuilder\Domain\ValueObjects\FormSettings;
use Illuminate\Database\QueryException;

class UpdateFormUseCase
{
    public function __construct(
        protected FormRepositoryInterface $repository,
        protected SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(int $id, UpdateFormData $data): ?Form
    {
        $form = $this->repository->findById($id);
        if (! $form) {
            return null;
        }

        $updateData = [
            'title' => $data->title,
            'description' => $data->description,
            'notification_emails' => $data->notificationEmails,
            'status' => $data->status,
        ];

        if ($data->fields !== null) {
            $updateData['fields'] = new FormFieldCollection($data->fields);
        }
        if ($data->settings !== null) {
            $updateData['settings'] = new FormSettings($data->settings);
        }
        if ($data->isDynamic !== null) {
            $updateData['is_dynamic'] = $data->isDynamic;
        }

        // baseAlias !== null только когда alias генерируем сами —
        // только в этом случае конфликт уникального индекса разрешим перегенерацией.
        $baseAlias = null;

        if ($data->alias !== null) {
            $updateData['alias'] = $data->alias;
        } elseif (empty($form->alias) && ! empty($data->title)) {
            $baseAlias = $this->slugGenerator->generate($data->title);
            if (trim($baseAlias) === '') {
                $baseAlias = 'form';
            }

            $updateData['alias'] = $this->makeUniqueAlias($baseAlias);
        }

        $attempts = 0;
        $maxAttempts = 10;

        while ($attempts < $maxAttempts) {
            $form->update($updateData);

            try {
                return $this->repository->save($form);
            } catch (QueryException $e) {
                // alias передан явно клиентом — это валидационный конфликт,
                // гонку перегенерацией не разрешить; прокидываем как 422/500 на валидации.
                if ($data->alias !== null || $baseAlias === null) {
                    throw $e;
                }

                $message = $e->getMessage();
                if (! str_contains($message, 'forms_alias_unique') && ! str_contains($message, 'Duplicate entry')) {
                    throw $e;
                }

                // Race condition: alias заняли между findByAlias и UPDATE.
                // Перегенерируем суффикс и повторяем сохранение.
                $updateData['alias'] = $baseAlias.'-'.bin2hex(random_bytes(4));
                $attempts++;
            }
        }

        throw new \RuntimeException('Unable to generate unique alias after '.$maxAttempts.' attempts');
    }

    private function makeUniqueAlias(string $base): string
    {
        $alias = $base;
        $suffix = 1;
        while ($this->repository->findByAlias($alias) !== null) {
            $alias = $base.'-'.$suffix++;
        }

        return $alias;
    }
}
