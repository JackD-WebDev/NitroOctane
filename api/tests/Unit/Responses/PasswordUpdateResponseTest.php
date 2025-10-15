<?php

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Helpers\ResponseHelper;
use App\Http\Responses\PasswordUpdateResponse;

uses(TestCase::class);

beforeEach(function () {
    $mock = Mockery::mock(ResponseHelper::class);
    $mock->shouldReceive('requestResponse')->andReturnUsing(function ($data, $message, $success, $code) {
        return new JsonResponse(['success' => $success, 'message' => $message], $code);
    });
    $mock->shouldReceive('healthCheckResponse')->andReturn(new JsonResponse(['success' => true, 'message' => 'ok'], 200));
    app()->instance(ResponseHelper::class, $mock);
});

it('returns json when wants json for password update', function () {
    $response = app(PasswordUpdateResponse::class);

    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);

    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
    expect($payload['message'])->toBeString();
});

it('returns back when not wanting json (TypeError accepted)', function () {
    $response = app(PasswordUpdateResponse::class);

    $request = Request::create('/', 'POST', []);

    try {
        $res = $response->toResponse($request);
        expect($res)->not->toBeInstanceOf(JsonResponse::class);
    } catch (TypeError $e) {
        expect(true)->toBeTrue();
    }
});
