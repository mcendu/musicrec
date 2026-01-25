<?php

use App\Models\User;
use Database\Seeders\TrackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class);

test('track can be retrieved', function () {
    /** @var Tests\TestCase $this */
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

test('track can be created by authenticated user', function () {
    /** @var \Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/tracks/', [
            'name' => '강남스타일', // Gangnam Style
            'artist' => '싸이', // Psy
        ])
        ->assertStatus(201)
        ->assertJson([]);
    expect($response['location'])->toBeString();
    $location = $response['location'];

    $this->actingAsGuest()->get($location)
        ->assertStatus(200)
        ->assertJson([
            'name' => '강남스타일',
            'artist' => '싸이',
        ]);
});

test('the same track cannot be created twice', function () {
    /** @var \Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/tracks/', [
            'name' => '강남스타일', // Gangnam Style
            'artist' => '싸이', // Psy
        ])
        ->assertStatus(201)
        ->assertJson([]);

    $this->actingAs($user)
        ->postJson('/api/v1/tracks/', [
            'name' => '강남스타일',
            'artist' => '싸이',
        ])
        ->assertStatus(409)
        ->assertJson(['error' => 'record_exists']);
});

test('unauthenticated user cannot create tracks', function () {
    /** @var \Tests\TestCase $this */

    $this->actingAsGuest()
        ->postJson('/api/v1/tracks/', [
            'name' => '강남스타일', // Gangnam Style
            'artist' => '싸이', // Psy
        ])
        ->assertStatus(403);
});

test('track can be deleted by authenticated user', function () {
    /** @var \Tests\TestCase $this */
    $this->seed(TrackSeeder::class);
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/api/v1/tracks/1')
        ->assertStatus(204);

    $this->actingAsGuest()->get('/api/v1/tracks/2147483647')
        ->assertStatus(404)
        ->assertJson(['error' => 'no_such_record']);
});

test('attempting to delete nonexistent track fails', function () {
    /** @var Tests\TestCase $this */
    $this->seed(TrackSeeder::class);
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/api/v1/tracks/1')
        ->assertStatus(204);

    $this->actingAs($user)->delete('/api/v1/tracks/1')
        ->assertStatus(404)
        ->assertJson(['error' => 'no_such_record']);
});

test('unauthenticated user cannot delete tracks', function () {
    /** @var \Tests\TestCase $this */
    $this->seed(TrackSeeder::class);

    $this->actingAsGuest()->delete(
        '/api/v1/tracks/1',
        ['Accept' => 'application/json']
    )
        ->assertStatus(403);
});
