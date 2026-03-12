<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Http\Controllers\RecommendController;

Route::prefix('/v1')->group(function () {
    Route::controller(Controllers\TokenController::class)
        ->name('token.')
        ->prefix('/token')
        ->group(function () {
            Route::post('/', 'create')->name('create');
        })
        ->middleware(['auth:sanctum']);

    Route::controller(Controllers\ArtistController::class)
        ->name('artist.')
        ->prefix('/artists')
        ->group(function () {
            Route::get('/{id}', 'show')->name('show');
            Route::get('/{id}/tracks', 'tracks')->name('tracks');
        });

    Route::controller(Controllers\TrackController::class)
        ->name('track.')
        ->prefix('/tracks')
        ->group(function () {
            Route::get('/{id}', 'show')->name('show');

            Route::middleware(['auth:sanctum', 'ability:manage-tracks'])
                ->group(function () {
                    Route::post('/', 'create')->name('create');
                    Route::delete('/{id}', 'delete')->name('delete');
                });
        });

    Route::get('/random', [Controllers\RecommendController::class, 'random'])
        ->name('recommend.random');
    Route::get('/recommend', [Controllers\RecommendController::class, 'recommend'])
        ->name('recommend.recommend');
});
