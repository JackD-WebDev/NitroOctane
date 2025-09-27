<?php

use App\Http\Responses\RegisterResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

it('returns json payload with user after registration', function () {
    $response = app(RegisterResponse::class);
    $user = User::factory()->make();
    $request = Request::create('/', 'POST', []);
    $request->setUserResolver(fn () => $user);
    $request->headers->set('Accept', 'application/json');
    $res = $response->toResponse($request);
    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['user']['id'])->toBe($user->id);
    expect($payload['redirect_url'])->toBeString();
});
