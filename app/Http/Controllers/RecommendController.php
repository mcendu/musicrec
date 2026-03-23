<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

use App\Models\Track;

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
            return Inertia::render('Recommend', ['recommendations' => $tracks]);
        }
    }

    /** Recommend a list of tracks similar to a specific track. */
    function similarToTrack(Request $req, int $id)
    {
        $rowcount = intval($req->query("count", "10"));

        // fetch track's own data for quicker access from the frontend
        $track = Track::find($id);
        if (!$track) {
            return errorResponse(
                $req,
                'no_such_record',
                "No track with ID $id exists",
                Response::HTTP_NOT_FOUND
            );
        }

        // recommend tracks
        // TODO: port to engines other than Postgres
        $tracks = array_map(
            function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'artist' => [
                        'id' => $t->artist_id,
                        'name' => $t->artist_name,
                    ],
                ];
            },
            DB::select(
                'SELECT t.id, t.name, t.artist_id, a.name AS artist_name
                    FROM tracks AS t LEFT JOIN artists AS a ON t.artist_id = a.id
                    WHERE t.id != :id
                    ORDER BY t.tags <=> (SELECT tags FROM tracks WHERE id = :id)
                    LIMIT :count;',
                ['id' => $id, 'count' => $rowcount]
            )
        );

        $data = [
            'track' => [
                'id' => $track->id,
                'name' => $track->name,
                'artist' => [
                    'id' => $track->artist->id,
                    'name' => $track->artist->name,
                ],
                'urls' => $track->urls->toResourceCollection()->all(),
            ],
            'recommendations' => $tracks,
        ];

        if ($req->has("isApiReq")) {
            return response()->json($data);
        } else {
            return Inertia::render('Recommend', $data);
        }
    }
}
