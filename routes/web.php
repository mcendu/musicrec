<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers;

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', [Controllers\RecommendController::class, 'random']);
Route::get('/artists/{id}', [Controllers\ArtistController::class, 'tracks']);
Route::get('/tracks/{id}', [Controllers\RecommendController::class, 'similarToTrack']);

require __DIR__ . '/settings.php';
