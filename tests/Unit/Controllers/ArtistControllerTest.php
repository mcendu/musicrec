<?php

use Database\Seeders\TrackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class);

test('artist can be retrieved', function () {
    /** @var Tests\TestCase $this */
    $this->seed(TrackSeeder::class);

    $this->get('/api/v1/artists/1')
        ->assertStatus(200)
        ->assertJson([
            'id' => 1,
            'name' => 'Justin Bieber',
        ]);
});
