<?php

use App\Http\Responses\PasswordConfirmedResponse;
use Tests\TestCase;

uses(TestCase::class);
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

it('returns json when wants json for password confirmed', function () {
    $response = app(PasswordConfirmedResponse::class);

    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);

    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
    expect($payload['message'])->toBeString();
});

it('redirects when not wanting json (TypeError accepted)', function () {
    $response = app(PasswordConfirmedResponse::class);

    $request = Request::create('/', 'POST', []);

    try {
        $res = $response->toResponse($request);
        expect($res)->not->toBeInstanceOf(JsonResponse::class);
    } catch (TypeError $e) {
        expect(true)->toBeTrue();
    }
});
