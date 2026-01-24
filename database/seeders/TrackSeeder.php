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
            $trackId = DB::table('tracks')->insertGetId([
                'name' => 'Baby ft. Ludacris',
                'artist' => 'Justin Bieber',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            DB::table('urls')->insert([
                'track_id' => $trackId,
                'website' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=kffacxfA7G4',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        });
    }
}
