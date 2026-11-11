<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Infrastructure\Models\FormSubmissionModel;
use App\Modules\SettingManager\Infrastructure\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\TestCase;

/**
 * Капча в публичном submit форм (часть 2 подсистемы капчи):
 * тумблер формы + обязательный captcha_token при активном драйвере.
 */
class WebFormCaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Лимиты submit проверяет FormSubmitThrottleTest — здесь не мешают.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_submit_with_valid_captcha_token_succeeds(): void
    {
        $this->enableTurnstile();

        $form = (new FormModelFactory)->create(['captcha_enabled' => true]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Тест Пользователь',
            'message' => 'Проверка капчи.',
            'captcha_token' => 'XXXX.DUMMY.TOKEN.OK',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Форма успешно отправлена');

        $this->assertDatabaseHas('form_submissions', ['form_id' => $form->id]);

        // Служебный токен в сохранённой заявке не оседает.
        $submission = FormSubmissionModel::where('form_id', $form->id)->firstOrFail();
        $this->assertArrayNotHasKey('captcha_token', $submission->data ?? []);
    }

    public function test_submit_without_token_is_rejected_with_422(): void
    {
        $this->enableTurnstile();

        $form = (new FormModelFactory)->create(['captcha_enabled' => true]);

        $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Тест Пользователь',
            'message' => 'Без токена.',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Проверка капчи не пройдена. Обновите страницу и попробуйте отправить форму ещё раз.');

        $this->assertDatabaseMissing('form_submissions', ['form_id' => $form->id]);
    }

    public function test_submit_with_rejected_token_is_rejected_with_422(): void
    {
        $this->setSetting('captcha_driver', 'turnstile');
        $this->setSetting('captcha_secret', 'secret-1');
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $form = (new FormModelFactory)->create(['captcha_enabled' => true]);

        $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Тест Пользователь',
            'message' => 'Битый токен.',
            'captcha_token' => 'bad-token',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('form_submissions', ['form_id' => $form->id]);
    }

    public function test_form_without_captcha_accepts_submit_without_token(): void
    {
        // Драйвер капчи активен, но тумблер формы выключен — токен не требуется.
        $this->enableTurnstile();

        $form = (new FormModelFactory)->create(['captcha_enabled' => false]);

        $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Тест Пользователь',
            'message' => 'Тумблер выключен.',
        ])->assertOk();

        // Провайдер капчи даже не вызывался.
        Http::assertNothingSent();
    }

    public function test_captcha_enabled_with_none_driver_does_not_require_token(): void
    {
        // Тумблер включён, но драйвер не выбран (настройки нет, fallback на none).
        $form = (new FormModelFactory)->create(['captcha_enabled' => true]);

        $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Тест Пользователь',
            'message' => 'Driver=none.',
        ])->assertOk();
    }

    public function test_show_exposes_captcha_widget_config_when_enabled(): void
    {
        $this->enableTurnstile();

        $form = (new FormModelFactory)->create(['captcha_enabled' => true]);

        $this->getJson("/api/forms/{$form->id}")
            ->assertOk()
            ->assertJsonPath('captcha_enabled', true)
            ->assertJsonPath('captcha.driver', 'turnstile')
            ->assertJsonPath('captcha.site_key', 'site-key-1');
    }

    public function test_show_omits_captcha_config_when_disabled(): void
    {
        $form = (new FormModelFactory)->create(['captcha_enabled' => false]);

        $this->getJson("/api/forms/{$form->id}")
            ->assertOk()
            ->assertJsonPath('captcha_enabled', false)
            ->assertJsonMissingPath('captcha');
    }

    private function enableTurnstile(): void
    {
        $this->setSetting('captcha_driver', 'turnstile');
        $this->setSetting('captcha_site_key', 'site-key-1');
        $this->setSetting('captcha_secret', 'secret-1');

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);
    }

    private function setSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => 'string', 'group' => 'captcha', 'label' => $key, 'order' => 0]
        );
    }
}
