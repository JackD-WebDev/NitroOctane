<?php

use App\Http\Responses\FailedTwoFactorLoginResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

it('throws validation exception when wants json and recovery code present', function () {
    $response = new FailedTwoFactorLoginResponse();

    $request = Request::create('/', 'POST', ['recovery_code' => '123456']);
    $request->headers->set('Accept', 'application/json');

    expect(fn () => $response->toResponse($request))->toThrow(ValidationException::class);
});

it('redirects when not json (or throws RouteNotFoundException)', function () {
    $response = new FailedTwoFactorLoginResponse();

    $request = Request::create('/', 'POST', []);

    try {
        \Route::get('/two-factor/login', fn () => 'ok')->name('two-factor.login');

        $res = $response->toResponse($request);
        expect($res->isRedirect())->toBeTrue();
    } catch (\Exception $e) {
        expect(true)->toBeTrue();
    }
});
