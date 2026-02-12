<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class TrackController
{
    function show(int $id): JsonResponse
    {
        $tracks = DB::select(
            'SELECT
                tracks.id AS id,
                tracks.name AS name,
                artists.name AS artist,
                tracks.updated_at AS updated_at
            FROM tracks
                LEFT JOIN artists ON artists.id = tracks.artist_id
            WHERE tracks.id = :id',
            ['id' => $id]
        );
        $urls = DB::select(
            'SELECT website, url FROM urls WHERE track_id=:id',
            ['id' => $id]
        );

        if (count($tracks) == 0) {
            return response()->json([
                'error' => 'no_such_record',
                'message' => "No track with ID $id exists",
            ], Response::HTTP_NOT_FOUND);
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
            return response()->json([
                'error' => 'record_exists',
                'message' => "Track '$artist - $name' already exists"
            ], Response::HTTP_CONFLICT);
        }

        return response()
            ->json([
                'location' => route('track.show', ['id' => $id])
            ], Response::HTTP_CREATED)
            ->header('Location', route('track.show', ['id' => $id]));
    }

    function delete(int $id)
    {
        $count = DB::transaction(function () use ($id) {
            DB::delete('DELETE FROM urls WHERE track_id=:id', ['id' => $id]);
            return DB::delete('DELETE FROM tracks WHERE id=:id', ['id' => $id]);
        });

        if ($count == 0) {
            return response()->json([
                'error' => 'no_such_record',
                'message' => "No track with ID $id exists"
            ], Response::HTTP_NOT_FOUND);
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
