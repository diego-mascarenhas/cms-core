<?php

use Idoneo\CmsCore\Http\Controllers\Api\PostController;
use Idoneo\CmsCore\Http\Middleware\AuthenticateApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', AuthenticateApiToken::class]);

// Posts API - Requires authentication via Bearer token or APP_TOKEN from .env
Route::middleware(['auth:sanctum', AuthenticateApiToken::class])->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);
});
