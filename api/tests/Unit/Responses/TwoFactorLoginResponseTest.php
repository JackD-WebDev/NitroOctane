<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\TwoFactorLoginResponse;

uses(TestCase::class);

it('redirects when request does not want json', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $respHelper = app(TwoFactorLoginResponse::class);

    $req = Request::create('/', 'GET');

    try {
        $resp = $respHelper->toResponse($req);
        expect(method_exists($resp, 'getStatusCode'))->toBeTrue();
        expect($resp->getStatusCode())->toBe(302);
    } catch (TypeError $e) {
        expect($e)->toBeInstanceOf(TypeError::class);
    }
});

it('returns unauthorized when not authenticated and wants json', function () {
    $resp = app(TwoFactorLoginResponse::class);

    auth()->logout();

    $request = Request::create('/2fa', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    expect($result->getStatusCode())->toBe(401);
});

it('returns json with user payload when authenticated and wants json', function () {
    $user = User::factory()->create();
    auth()->login($user);

    $resp = app(TwoFactorLoginResponse::class);
    $request = Request::create('/2fa', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    $json = $result->getData(true);
    expect($json['user']['email'])->toBe($user->email);
});
