<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArtistController
{
    function show(int $id): JsonResponse
    {
        $artist = DB::table('artists')->where('id', '=', $id)->first();

        if (!$artist) {
            return response()->json([
                'error' => 'no_such_record',
                'message' => 'no artist exists with the specified id',
            ], 404);
        }

        return response()->json([
            'id' => $id,
            'name' => $artist->name,
        ])
            ->header(
                'Last-Modified',
                date_create($artist->updated_at)->format(DATE_RFC7231)
                );
    }

    function tracks(Request $req, int $id): JsonResponse
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

        return response()->json([
            'tracks' => $tracks->items(),
            'navigation' => [
                'limit' => $limit,
                'next' => is_null($next) ? null : $next->encode(),
                'prev' => is_null($prev) ? null : $prev->encode(),
            ]
        ]);
    }
}
