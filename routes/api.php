<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

Route::prefix('/v1')->group(function () {
    Route::controller(Controllers\TokenController::class)
        ->name('token.')
        ->prefix('/token')
        ->group(function () {
            Route::post('/', 'create')->name('create');
        })
        ->middleware(['auth:sanctum']);

    Route::controller(Controllers\TrackController::class)
        ->name('track.')
        ->prefix('/tracks')
        ->group(function () {
            Route::get('/{id}', 'show')->name('show');
        });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
