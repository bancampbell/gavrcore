<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * C1 (docs/SECURITY.md): .env недоступен из веба.
 * Публичный корень — public/, .env лежит вне его и не должен отдаваться.
 */
class DotEnvAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_env_file_is_not_served(): void
    {
        $response = $this->get('/.env');

        $this->assertContains(
            $response->status(),
            [403, 404],
            'GET /.env вернул '.$response->status().' — .env доступен из веба!'
        );
    }
}
