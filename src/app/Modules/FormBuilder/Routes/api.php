<?php

use App\Modules\FormBuilder\Infrastructure\Http\Controllers\WebFormController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('api/forms')->group(function () {
    Route::get('/{id}', [WebFormController::class, 'show']);
    Route::post('/{id}/submit', [WebFormController::class, 'submit'])
        ->middleware('throttle:form-submit');
});