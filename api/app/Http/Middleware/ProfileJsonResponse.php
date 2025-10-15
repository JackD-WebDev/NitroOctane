<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/*
|-------------------------------------------------------------------------------
| Middleware to Profile JSON Responses
|-------------------------------------------------------------------------------
| This middleware checks if the application is in debug mode and if the response
| is a JSON response. If so, it appends debug information to the response
| when the request contains a specific query parameter.
| This is useful for debugging and profiling API responses.
*/
class ProfileJsonResponse
{
    /**
     * Handle an incoming request and profile the JSON response if debug mode is enabled.
     *
     * @param  Closure(Request): (\Illuminate\Http\Response)  $next
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (! app()->bound('debugbar') || ! app('debugbar')->isEnabled()) {
            return $response;
        }

        if ($response instanceof JsonResponse && $request->has('_debug')) {
            $response->setData([
                ...$response->getData(true),
                '_debugbar' => Arr::only(app('debugbar')->getData(), 'queries'),
            ]);
        }

        return $response;
    }
}
