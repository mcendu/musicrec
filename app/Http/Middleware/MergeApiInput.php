<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeApiInput
{
    /**
     * Set isApiReq to true for the request. The presence of isApiReq
     * can be used to determine whether to send an HTML/Inertia or JSON
     * response, allowing the same controller to handle both browser and
     * API requests.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(['isApiReq' => true]);
        return $next($request);
    }
}
