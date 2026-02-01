<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes :-
Route::post('/register',RegisterController::class);
Route::post('/login',LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',LogoutController::class);

    // User Routes :-
    Route::apiResource('users', UserController::class)
        ->whereNumber('user');

    // Post Routes :-
    Route::apiResource('posts', PostController::class)
        ->whereNumber('post');

    // Comment Routes :-
    Route::apiResource(name:'posts.comments',controller: CommentController::class)->shallow()
        ->whereNumber('post')
        ->whereNumber('comment');

    // Like Route :-
    Route::post('posts/{post}/like',LikeController::class)
        ->whereNumber('post');

    // for test the current user just :-
    Route::get('/user', fn (Request $request) => $request->user());
});
