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
        ]);
    }
}
