<?php

use App\Http\Responses\LogoutResponse;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

uses(TestCase::class);

it('returns json with redirect_url when called', function () {
    $resp = app(LogoutResponse::class);

    $request = Request::create('/logout', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    $json = $result->getData(true);
    expect($json['redirect_url'])->toContain('/login');
});
