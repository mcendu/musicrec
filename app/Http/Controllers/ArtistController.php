<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ArtistController
{
    function show(Request $req, int $id)
    {
        $artist = DB::table('artists')->where('id', '=', $id)->first();

        if (!$artist) {
            return response()->json([
                'error' => 'no_such_record',
                'message' => 'no artist exists with the specified id',
            ], 404);
        }

        $data = [
            'id' => $id,
            'name' => $artist->name,
        ];

        if ($req->has("isApiReq")) {
            return response()->json($data)
                ->header(
                    'Last-Modified',
                    date_create($artist->updated_at)->format(DATE_RFC7231)
                );
        } else {
            return Inertia::render('Artist', $data);
        }
    }

    function tracks(Request $req, int $id)
    {
        $artistExists = DB::table('artists')->where('id', '=', $id)->exists();
        $limit = $req->input('limit', 20);

        if (!$artistExists) {
            return response()->json([
                'error' => 'no_such_record',
                'message' => 'no artist exists with the specified id',
            ], 404);
        }

        $tracks = DB::table('tracks')
            ->where('artist_id', '=',  $id)
            ->orderBy('id')
            ->cursorPaginate($limit, ['id', 'name'], 'cursor');

        $next = $tracks->nextCursor();
        $prev = $tracks->previousCursor();

        $data = [
            'tracks' => $tracks->items(),
            'navigation' => [
                'limit' => $limit,
                'next' => is_null($next) ? null : $next->encode(),
                'prev' => is_null($prev) ? null : $prev->encode(),
            ]
        ];

        if ($req->has('isApiReq')) {
            return response()->json($data);
        } else {
            return Inertia::render('ArtistTracks', $data);
        }
    }
}
