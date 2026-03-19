<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

function errorResponse(Request $request, string $error, string $message, int $status): Response
{
    $data = [
        'error' => $error,
        'message' => $message,
    ];

    if ($request->has('isApiReq')) {
        return response()->json($data, $status);
    } else {
        return Inertia::render('Error', $data)->toResponse($request)->setStatusCode($status);
    }
}
