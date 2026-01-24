<?php

use Database\Seeders\TrackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class);

test('track data can be retrieved', function () {
    $this->seed(TrackSeeder::class);

    $this->get('/api/v1/tracks/1')
        ->assertStatus(200)
        ->assertJson([
            'id' => 1,
            'name' => 'Baby ft. Ludacris',
            'artist' => 'Justin Bieber',
            'urls' => [
                [
                    'website' => 'youtube',
                    'url' => 'https://www.youtube.com/watch?v=kffacxfA7G4',
                ]
            ],
        ]);
});

test('retrieval of non-existent track results in an error', function () {
    $this->seed(TrackSeeder::class);

    $this->get('/api/v1/tracks/2147483647')
        ->assertStatus(404)
        ->assertJson(['error' => 'no_such_item']);
});
