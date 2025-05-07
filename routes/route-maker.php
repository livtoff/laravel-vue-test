<?php

use Illuminate\Support\Facades\Route;

// /
Route::get('/home/{id}', [\App\Http\Controllers\HomeController::class, 'show'])->name('Controllers.HomeController.show');

