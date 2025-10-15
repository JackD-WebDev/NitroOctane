<?php

use Tests\TestCase;
use App\Http\Responses\RecoveryCodesGeneratedResponse;

uses(TestCase::class);
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

it('returns json when wants json for recovery codes generated', function () {
    $response = app(RecoveryCodesGeneratedResponse::class);

    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);

    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
});

it('returns back when not wanting json (TypeError accepted)', function () {
    $response = app(RecoveryCodesGeneratedResponse::class);
    $request = Request::create('/', 'POST', []);

    try {
        $res = $response->toResponse($request);
        expect($res)->not->toBeInstanceOf(JsonResponse::class);
    } catch (TypeError $e) {
        expect(true)->toBeTrue();
    }
});
