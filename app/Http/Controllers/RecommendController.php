<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RecommendController
{
    /** Recommend a list of random tracks. */
    function random(Request $req)
    {
        $rowcount = intval($req->query("count", "10"));

        $tracks = DB::select(
            'SELECT id, name, artist FROM tracks
                TABLESAMPLE SYSTEM_ROWS(:count)',
            ['count' => $rowcount]
        );

        return response()->json($tracks);
    }

    /** Recommend a list of tracks similar to a specific track. */
    function recommend(Request $req)
    {
        $rowcount = intval($req->query("count", "10"));

        $tracks = DB::select(
            'SELECT id, name, artist FROM tracks
                TABLESAMPLE SYSTEM_ROWS(:count)',
            ['count' => $rowcount]
        );

        return response()->json($tracks);
    }
}
