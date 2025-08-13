<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;


/*
|-------------------------------------------------------------------------------
| Middleware to Set Application Locale from Request
|-------------------------------------------------------------------------------
| This middleware sets the application's locale based on the 'Accept-Language'
| header in the incoming request. If the locale is supported, it updates the
| application's locale accordingly.
| This is useful for localizing API responses for different users.
*/
class LocalizationResponse
{
    /**
     * Handle an incoming request and set the application locale based on the request header.
     *
     * @param  Request  $request
     * @param  Closure(Request): (\Illuminate\Http\Response)  $next
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language');
        if ($locale && in_array($locale, config('app.locales', ['en_US']))) {
            App::setLocale($locale);
        }

        $response = $next($request);

        return $response;
    }
}