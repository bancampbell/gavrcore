<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\CreateFormData;
use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Events\FormCreated;
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
                    captchaEnabled: $data->captchaEnabled,
                    submissionsCount: 0,
                );

                $saved = $this->repository->save($form);
                event(new FormCreated($saved));

                return $saved;
            } catch (QueryException $e) {
                // Если alias был передан явно — это не race condition, а валидационная ошибка клиента
                if ($data->alias) {
                    throw $e;
                }

                if (! $this->isUniqueViolation($e)) {
                    throw $e;
                }

                // Race condition: кто-то занял alias между findByAlias и INSERT
                $alias = $this->makeUniqueAlias($baseAlias.'-'.bin2hex(random_bytes(4)));
                $attempts++;
            }
        }

        throw new \RuntimeException('Unable to generate unique alias after '.$maxAttempts.' attempts');
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        // MySQL: 1062, PostgreSQL: 23505, SQLite: 8/19/1555/2067 (SQLITE_CONSTRAINT*).
        // Драйвер pgsql кладёт в errorInfo[1] свой код (7), а не SQLSTATE —
        // 23505 ищем в errorInfo[0]/getCode() (эталон — UserManager).
        $codes = array_filter([
            (string) ($e->errorInfo[0] ?? ''),
            (string) $e->getCode(),
        ]);

        return count(array_intersect($codes, ['1062', '23505', '8', '19', '1555', '2067'])) > 0;
    }

    private function makeUniqueAlias(string $base): string
    {
        $alias = $base;
        if ($this->repository->findByAlias($alias) === null) {
            return $alias;
        }

        do {
            $alias = $base.'-'.bin2hex(random_bytes(4));
        } while ($this->repository->findByAlias($alias) !== null);

        return $alias;
    }
}
