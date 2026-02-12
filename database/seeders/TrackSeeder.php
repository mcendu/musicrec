<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $time = date_create_immutable()->format(DATE_ATOM);
            DB::table('artists')->insertOrIgnore([
                'id' => 1,
                'name' => 'Justin Bieber',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            DB::table('tracks')->insertOrIgnore([
                'id' => 1,
                'name' => 'Baby ft. Ludacris',
                'artist_id' => 1,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            DB::table('urls')->insertOrIgnore([
                'track_id' => 1,
                'website' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=kffacxfA7G4',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        });
    }
}
