<?php

use App\Http\Middleware\ProfileJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

it('returns unchanged response when debugbar is not bound', function () {
    // Ensure debugbar is not bound in the container
    if (app()->bound('debugbar')) {
        app()->forgetInstance('debugbar');
    }

    $middleware = new ProfileJsonResponse();

    $request = Request::create('/test', 'GET', ['_debug' => '1']);

    $next = function ($req) {
        return response()->json(['foo' => 'bar']);
    };

    $response = $middleware->handle($request, $next);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    $data = $response->getData(true);
    expect(isset($data['_debugbar']))->toBeFalse();
    expect($data['foo'])->toBe('bar');
});

it('appends debugbar queries when debugbar enabled and _debug present', function () {
    // Bind a simple debugbar-like object to the container
    $debug = new class {
        public function isEnabled()
        {
            return true;
        }

        public function getData()
        {
            return [
                'queries' => ['SELECT 1', 'SELECT 2'],
                'messages' => ['a' => 'b']
            ];
        }
    };

    app()->instance('debugbar', $debug);

    $middleware = new ProfileJsonResponse();

    $request = Request::create('/test', 'GET', ['_debug' => '1'], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $next = function ($req) {
        return response()->json(['hello' => 'world']);
    };

    $response = $middleware->handle($request, $next);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    $data = $response->getData(true);
    expect(isset($data['_debugbar']))->toBeTrue();
    expect($data['_debugbar'])->toHaveKey('queries');
    expect($data['_debugbar']['queries'])->toBeArray();
});

it('does not append debugbar when debugbar is disabled', function () {
    $debug = new class {
        public function isEnabled()
        {
            return false;
        }

        public function getData()
        {
            return ['queries' => ['SELECT 1']];
        }
    };

    app()->instance('debugbar', $debug);

    $middleware = new ProfileJsonResponse();

    $request = Request::create('/test', 'GET', ['_debug' => '1']);

    $next = function ($req) {
        return response()->json(['x' => 'y']);
    };

    $response = $middleware->handle($request, $next);

    $data = $response->getData(true);
    expect(isset($data['_debugbar']))->toBeFalse();
});
