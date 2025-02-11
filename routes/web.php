<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/oauth/login', [AuthController::class, 'redirectToMicrosoft']);
Route::get('/oauth/callback', [AuthController::class, 'handleMicrosoftCallback']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/microsoft/callback', [AuthController::class, 'handleMicrosoftCallback'])->name('auth.microsoft.callback');