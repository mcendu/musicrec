<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecommendController
{
    /** Recommend a list of random tracks. */
    function random(Request $req)
    {
        $rowcount = intval($req->query("count", "10"));

        $tracks = DB::select(
            'SELECT id, name, artist_id FROM tracks
                TABLESAMPLE SYSTEM_ROWS(:count)',
            ['count' => $rowcount]
        );

        if ($req->has("isApiReq")) {
            return response()->json($tracks);
        } else {
            return Inertia::render('Recommend', ['tracks' => $tracks]);
        }
    }

    /** Recommend a list of tracks similar to a specific track. */
    function similarToTrack(Request $req, int $id)
    {
        $rowcount = intval($req->query("count", "10"));

        // TODO: port to engines other than Postgres
        $tracks = DB::select(
            'SELECT id, name, artist_id FROM tracks
                WHERE id != :id
                ORDER BY tags <=> (SELECT tags FROM tracks WHERE id = :id)
                LIMIT :count;',
            ['id' => $id, 'count' => $rowcount]
        );

        return response()->json($tracks);
    }
}
