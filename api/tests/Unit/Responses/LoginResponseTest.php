<?php

use App\Http\Responses\LoginResponse;
use App\Http\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

uses(TestCase::class);

it('returns unauthorized for guests when requesting json', function () {
    $resp = app(LoginResponse::class);

    $request = Request::create('/login', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    auth()->logout();

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    expect($result->getStatusCode())->toBe(401);
});

it('returns json with user payload when authenticated and wants json', function () {
    $user = User::factory()->create();

    auth()->login($user);

    $resp = app(LoginResponse::class);
    $request = Request::create('/login', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    $json = $result->getData(true);
    expect($json['user']['email'])->toBe($user->email);
    expect($json['redirect_url'])->toBe(config('app.frontend_url'));
});

it('returns unauthorized for guests when not requesting json', function () {
    $resp = app(LoginResponse::class);

    $request = Request::create('/login', 'GET');

    auth()->logout();

    $result = $resp->toResponse($request);

    // Non-json unauthorized should still be a JsonResponse via ResponseHelper
    expect($result)->toBeInstanceOf(JsonResponse::class);
    expect($result->getStatusCode())->toBe(401);
});

it('returns redirect payload when authenticated and not wants json', function () {
    $user = User::factory()->create();

    auth()->login($user);

    $resp = app(LoginResponse::class);
    $request = Request::create('/login', 'GET');

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    $json = $result->getData(true);
    // When not wantsJson the response still contains redirect_url in the payload
    expect($json['redirect_url'])->toBe(config('app.frontend_url'));
    expect($json['success'])->toBeTrue();
});

it('logs remember token when remember is requested', function () {
    $user = User::factory()->create(['remember_token' => 'tok_123']);

    auth()->login($user);

    $resp = app(LoginResponse::class);

    // Create request that will interpret remember as true
    $request = Request::create('/login', 'GET', ['remember' => '1'], [], [], ['HTTP_ACCEPT' => 'application/json']);

    // Fake the logger and assert that an info entry is written
    \Illuminate\Support\Facades\Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) use ($user) {
            return str_contains($message, 'LoginResponse: Remember token requested')
                && isset($context['user_id'])
                && $context['user_id'] === $user->id;
        });

    $result = $resp->toResponse($request);
    expect($result->getStatusCode())->toBe(200);
});

it('returns two_factor true when user has two_factor_secret', function () {
    $user = User::factory()->create(['two_factor_secret' => 'secret123']);

    auth()->login($user);

    $resp = app(LoginResponse::class);
    $request = Request::create('/login', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);
    $json = $result->getData(true);

    expect($json['two_factor'])->toBeTrue();
});
