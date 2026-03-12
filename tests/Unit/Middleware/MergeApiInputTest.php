<?php

use App\Http\Middleware\MergeApiInput;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('isApiReq is merged into request', function () {
    $middleware = new MergeApiInput();

    $request = Request::create('/foo', 'GET');

    $response = $middleware->handle($request, function (Request $req) {
        expect($req->has('isApiReq'))->toBeTrue();

        return new Response(status: 204);
    });

    expect($response->getStatusCode() == 204);
});
