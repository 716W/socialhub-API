<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResendVerficationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes :-
Route::post('/register',RegisterController::class);
Route::post('/login',LoginController::class);

// Email Verification Route (must be reachable from email clients without auth token)
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware('signed' , 'throttle:6,1')
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    // Resend Verification Email Route :-
    Route::post('/email/resend', ResendVerficationController::class)
        ->middleware('throttle:6,1');

    // Conventional Laravel endpoint name (kept alongside /email/resend)
    Route::post('/email/verification-notification', ResendVerficationController::class)
        ->middleware('throttle:6,1');

    // Email Verification Route (Mobile App) 
    Route::post('/mobile/verify',[MobileAuthController::class , 'verify']);
    Route::post('/mobile/resend',[MobileAuthController::class , 'resend']);

    // Logout Route :-
    Route::post('/logout',LogoutController::class);

    // Verified-only routes
    Route::middleware(EnsureEmailIsVerified::class)->group(function () {
        // User Routes :-
        Route::apiResource('users' , UserController::class);

        // Post Routes :-
        Route::apiResource('posts', PostController::class);

        // Comment Routes :-
        Route::apiResource(name:'posts.comments',controller: CommentController::class)->shallow();

        // Like Route :-
        Route::post('posts/{post}/like',LikeController::class);

        // Profile Routes :-
        Route::post('profile', ProfileController::class);
    });

    // for test the current user just :-
    Route::get('/user', fn (Request $request) => $request->user()->load('profile'));
});
