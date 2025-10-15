<?php

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Responses\FailedPasswordConfirmationResponse;

uses(TestCase::class);

it('throws ValidationException when wants json', function () {
    $resp = new FailedPasswordConfirmationResponse;

    $this->expectException(ValidationException::class);

    $request = Request::create('/confirm-password', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $resp->toResponse($request);
});

it('returns back with errors when not json', function () {
    $resp = new FailedPasswordConfirmationResponse;

    $this->expectException(TypeError::class);

    $request = Request::create('/confirm-password', 'POST');

    $resp->toResponse($request);
});
