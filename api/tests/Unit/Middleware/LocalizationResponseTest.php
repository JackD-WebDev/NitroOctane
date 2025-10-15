<?php

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Middleware\LocalizationResponse;

uses(TestCase::class);

it('sets app locale when Accept-Language present and supported', function () {
    $middleware = new LocalizationResponse;

    $req = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'es_US']);

    config(['app.locales' => ['en_US', 'es_US']]);

    $called = false;
    $next = function ($request) use (&$called) {
        $called = true;

        return response('ok');
    };

    $resp = $middleware->handle($req, $next);

    expect($called)->toBeTrue();
    expect(App::getLocale())->toBe('es_US');
    expect($resp->getStatusCode())->toBe(200);
});

it('does not change locale when unsupported', function () {
    $middleware = new LocalizationResponse;

    $req = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'fr_FR']);

    config(['app.locales' => ['en_US', 'es_US']]);

    $called = false;
    $next = function ($request) use (&$called) {
        $called = true;

        return response('ok');
    };

    $resp = $middleware->handle($req, $next);

    expect($called)->toBeTrue();
    expect(App::getLocale())->not->toBe('fr_FR');
    expect($resp->getStatusCode())->toBe(200);
});
