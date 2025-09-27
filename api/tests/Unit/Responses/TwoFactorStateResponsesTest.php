<?php

use App\Http\Responses\TwoFactorConfirmedResponse;
use Tests\TestCase;
use App\Http\Responses\TwoFactorEnabledResponse;
use App\Http\Responses\TwoFactorDisabledResponse;
uses(TestCase::class);

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

it('returns json for two-factor confirmed when requested', function () {
    $response = app(TwoFactorConfirmedResponse::class);
    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);
    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
});

it('returns json for two-factor enabled when requested', function () {
    $response = app(TwoFactorEnabledResponse::class);
    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);
    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
});

it('returns json for two-factor disabled when requested', function () {
    $response = app(TwoFactorDisabledResponse::class);
    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);
    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
});
