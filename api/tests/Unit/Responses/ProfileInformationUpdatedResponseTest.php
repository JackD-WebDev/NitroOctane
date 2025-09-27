<?php

use App\Http\Responses\ProfileInformationUpdatedResponse;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $mock = Mockery::mock(ResponseHelper::class);
    $mock->shouldReceive('requestResponse')->andReturnUsing(function ($data, $message, $success, $code) {
        return new JsonResponse(['success' => $success, 'message' => $message], $code);
    });
    app()->instance(ResponseHelper::class, $mock);
});

it('returns json when requested', function () {
    $response = app(ProfileInformationUpdatedResponse::class);
    $request = Request::create('/', 'POST', []);
    $request->headers->set('Accept', 'application/json');

    $res = $response->toResponse($request);

    expect($res)->toBeInstanceOf(JsonResponse::class);
    $payload = $res->getData(true);
    expect($payload['success'])->toBeTrue();
    expect(array_key_exists('message', $payload))->toBeTrue();
});
