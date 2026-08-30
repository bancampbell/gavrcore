<?php

use App\Modules\MenuManager\Infrastructure\Http\Controllers\WebMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/menu/{alias}/tree', [WebMenuController::class, 'tree'])->name('web.menu.tree');