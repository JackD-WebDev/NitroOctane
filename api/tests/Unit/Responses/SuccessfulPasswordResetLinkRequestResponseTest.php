<?php

use App\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

uses(TestCase::class);

it('returns json when wants json', function () {
    $helper = app(ResponseHelper::class);
    $resp = new SuccessfulPasswordResetLinkRequestResponse($helper, 'passwords.sent');

    $request = Request::create('/password/email', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $result = $resp->toResponse($request);

    expect($result)->toBeInstanceOf(JsonResponse::class);
    expect($result->getStatusCode())->toBe(200);
});

it('throws TypeError when not json (redirect response mismatches union)', function () {
    $helper = app(ResponseHelper::class);
    $resp = new SuccessfulPasswordResetLinkRequestResponse($helper, 'passwords.sent');

    $request = Request::create('/password/email', 'POST');

    $this->expectException(TypeError::class);

    $resp->toResponse($request);
});
