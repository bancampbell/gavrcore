<?php

use App\Modules\CookieConsent\Infrastructure\Http\Controllers\CookieConsentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CookieConsent — публичные web-роуты согласия на cookies
|--------------------------------------------------------------------------
|
| Файл подключается CookieConsentServiceProvider с middleware-группой "web".
|
*/

Route::post('/cookie-consent/accept', [CookieConsentController::class, 'accept'])->name('cookie.consent.accept');
Route::post('/cookie-consent/decline', [CookieConsentController::class, 'decline'])->name('cookie.consent.decline');
