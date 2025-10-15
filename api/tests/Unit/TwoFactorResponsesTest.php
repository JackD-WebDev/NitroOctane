<?php

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Responses\FailedTwoFactorLoginResponse;

uses(TestCase::class);

beforeEach(function () {
    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }

    $mock = Mockery::mock(\App\Http\Helpers\ResponseHelper::class);
    $mock->shouldReceive('requestResponse')->andReturnUsing(function ($data, $message, $success, $code) {
        return new JsonResponse(['success' => $success, 'message' => $message], $code);
    });

    app()->instance(\App\Http\Helpers\ResponseHelper::class, $mock);
    $this->responseHelper = $mock;
});

it('returns unauthorized for guests when requesting 2fa json', function () {
    $request = Request::create('/2fa', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new TwoFactorLoginResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(401);
});

it('returns json for authenticated user when requesting 2fa json', function () {
    $user = new App\Models\User([
        'id' => 'test-user-id',
        'name' => '2FA User',
        'username' => 'twofa',
        'email' => 'twofa@example.com',
    ]);

    $request = Request::create('/2fa', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    $this->actingAs($user);

    $resp = (new TwoFactorLoginResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(200);
});

it('throws validation exception for failed two-factor login when json requested', function () {
    $request = Request::create('/2fa', 'POST', ['recovery_code' => 'wrong'], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    expect(fn () => (new FailedTwoFactorLoginResponse)->toResponse($request))
        ->toThrow(ValidationException::class);
});
