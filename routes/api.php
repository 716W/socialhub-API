<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes :-
Route::post('/Register',RegisterController::class);
Route::post('/login',LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',LogoutController::class);

    Route::apiResource('posts', PostController::class);

    // for test the current user just :-
    Route::get('/user', fn (Request $request) => $request->user());
});
