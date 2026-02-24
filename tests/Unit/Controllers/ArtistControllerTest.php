<?php

use App\Models\User;
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

test('attempt to retrieve unknown artist fails', function () {
    /** @var Tests\TestCase $this */
    $this->seed(TrackSeeder::class);

    $this->get('/api/v1/artists/2147483647')
        ->assertStatus(404)
        ->assertJson(['error' => 'no_such_record']);
});

test('creating track creates the associated artist', function () {
    /** @var \Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    // the artist should be created if non-existent
    $response = $this->actingAs($user)
        ->postJson('/api/v1/tracks/', [
            'name' => '강남스타일', // Gangnam Style
            'artist' => '싸이', // Psy
        ])
        ->assertStatus(201)
        ->assertJson([]);
    expect($response['location'])->toBeString();
    $gangnamLocation = $response['location'];

    $gangnam = $this->actingAsGuest()->get($gangnamLocation)
        ->assertStatus(200)
        ->assertJson([
            'artist' => [
                'name' => '싸이',
            ],
        ]);
    $artistId = $gangnam['artist']['id'];

    // if artist exists, it should not be recreated
    $response = $this->actingAs($user)
        ->postJson('/api/v1/tracks/', [
            'name' => '젠틀맨', // GENTLEMAN
            'artist' => '싸이', // Psy
        ])
        ->assertStatus(201)
        ->assertJson([]);
    expect($response['location'])->toBeString();
    $gentlemanLocation = $response['location'];

    $gentleman = $this->actingAsGuest()->get($gentlemanLocation)
        ->assertStatus(200)
        ->assertJson([
            'artist' => [
                'name' => '싸이',
            ],
        ]);
    expect($gentleman['artist']['id'])->toBe($artistId);
});
