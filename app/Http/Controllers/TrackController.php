<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TrackController
{
    function show(int $id)
    {
        $tracks = DB::select(
            'SELECT id, name, artist, updated_at FROM tracks WHERE id=:id',
            ['id' => $id]
        );
        $urls = DB::select(
            'SELECT website, url FROM urls WHERE track_id=:id',
            ['id' => $id]
        );

        if (count($tracks) == 0) {
            return response()->json([
                'error' => 'no_such_item',
                'message' => "No track with ID $id exists",
            ], 404);
        }

        $track = $tracks[0];

        return response()
            ->json([
                'id' => $track->id,
                'name' => $track->name,
                'artist' => $track->artist,
                'urls' => $urls,
            ])
            ->header('Last-Modified', $track->updated_at);
    }
}
