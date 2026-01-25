<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TokenController
{
    function create(Request $request)
    {
        $name = $request->input('name');
        $abilities = $request->input('abilities', []);

        $token = $request->user()->createToken($name, $abilities);
        return response()->json([
            'token' => $token->plainTextToken
        ], Response::HTTP_CREATED);
    }
}
