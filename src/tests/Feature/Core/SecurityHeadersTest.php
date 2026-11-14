<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B2 (docs/SECURITY.md): security-заголовки на web-ответах.
 * CSP — только Report-Only: enforce-заголовка Content-Security-Policy быть не должно.
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_contains_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function test_csp_is_report_only_not_enforced(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy-Report-Only');
        $response->assertHeaderMissing('Content-Security-Policy');
    }
}
