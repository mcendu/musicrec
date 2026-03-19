<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class TrackController
{
    function show(Request $req, int $id)
    {
        $tracks = DB::table('tracks')->where('id', '=', $id)->get();

        if (count($tracks) == 0) {
            return errorResponse(
                $req,
                'no_such_record',
                "No track with ID $id exists",
                Response::HTTP_NOT_FOUND
            );
        }

        $track = $tracks[0];
        $artist = DB::table('artists')
            ->where('id', '=', $track->artist_id)->first();
        $urls = DB::select(
            'SELECT website, url FROM urls WHERE track_id=:id',
            ['id' => $id]
        );

        $data = [
            'id' => $track->id,
            'name' => $track->name,
            'artist' => [
                'id' => $artist->id,
                'name' => $artist->name,
            ],
            'urls' => $urls,
        ];

        if ($req->has('isApiReq')) {
            return response()
                ->json($data)
                ->header(
                    'Last-Modified',
                    date_create($track->updated_at)->format(DATE_RFC7231)
                );
        } else {
            return Inertia::render('Track', $data);
        }
    }

    function create(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $artist = $request->input('artist');
        $time = date_create_immutable()->format(DATE_ATOM);
        $id = null;

        // check artist entry
        $artists = DB::table('artists')
            ->where('name', '=', $artist)->get();
        if (count($artists) != 0) {
            $artist_id = $artists[0]->id;
        } else {
            $artist_id = DB::table('artists')->insertGetId([
                'name' => $artist,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }

        try {
            $id = DB::scalar(
                'INSERT
                    INTO tracks (name, artist_id, created_at, updated_at)
                    VALUES (:name, :artist, :created_at, :created_at)
                    RETURNING id',
                [
                    'name' => $name,
                    'artist' => $artist_id,
                    'created_at' => $time,
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return errorResponse(
                $request,
                'record_exists',
                "Track '$artist - $name' already exists",
                Response::HTTP_CONFLICT
            );
        }

        return response()
            ->json([
                'location' => route('track.show', ['id' => $id])
            ], Response::HTTP_CREATED)
            ->header('Location', route('track.show', ['id' => $id]));
    }

    function delete(Request $req, int $id)
    {
        $count = DB::transaction(function () use ($id) {
            DB::delete('DELETE FROM urls WHERE track_id=:id', ['id' => $id]);
            return DB::delete('DELETE FROM tracks WHERE id=:id', ['id' => $id]);
        });

        if ($count == 0) {
            return errorResponse(
                $req,
                'no_such_record',
                "No track with ID $id exists",
                Response::HTTP_NOT_FOUND
            );
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
