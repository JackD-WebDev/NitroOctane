<?php

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Helpers\ResponseHelper;
use App\Http\Responses\VerifyEmailResponse;

uses(TestCase::class);

it('returns json when wants json', function () {
    $helper = app(ResponseHelper::class);
    $resp = new VerifyEmailResponse($helper);

    $request = Request::create('/email/verify', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
});

it('returns redirect when not json (TypeError expectation)', function () {
    $helper = app(ResponseHelper::class);
    $resp = new VerifyEmailResponse($helper);

    $request = Request::create('/email/verify', 'GET');

    $this->expectException(TypeError::class);

    $resp->toResponse($request);
});
