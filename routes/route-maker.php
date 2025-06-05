<?php

use Illuminate\Support\Facades\Route;

// /
Route::get('/', [\App\Http\Controllers\HomeController::class, 'show'])->name('Controllers.HomeController.show');

