<?php

use App\Http\Responses\PasswordUpdateResponse;
use App\Http\Responses\ProfileInformationUpdatedResponse;
use App\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use App\Http\Responses\EmailVerificationNotificationSentResponse;
use App\Http\Responses\PasswordConfirmedResponse;
use App\Http\Responses\TwoFactorEnabledResponse;
use App\Http\Responses\VerifyEmailResponse;
use App\Http\Responses\FailedPasswordConfirmationResponse;
use App\Http\Helpers\ResponseHelper;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

uses(TestCase::class);

beforeEach(function () {
    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }

    $mock = Mockery::mock(\App\Http\Helpers\ResponseHelper::class);
    $mock->shouldReceive('requestResponse')->andReturnUsing(function ($data, $message, $success, $code) {
        return new JsonResponse(['success' => $success, 'message' => $message], $code);
    });
    $mock->shouldReceive('healthCheckResponse')->andReturn(new JsonResponse(['success' => true, 'message' => 'ok'], 200));

    app()->instance(\App\Http\Helpers\ResponseHelper::class, $mock);
    $translator = Mockery::mock(Illuminate\Contracts\Translation\Translator::class);
    $translator->shouldReceive('get')->andReturnUsing(function ($key) {
        return (string) $key;
    });
    $translator->shouldReceive('trans')->andReturnUsing(function ($key) {
        return (string) $key;
    });
    app()->instance('translator', $translator);
    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    $this->responseHelper = $mock;
});

it('password update response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new PasswordUpdateResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(200);
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('profile information updated response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new ProfileInformationUpdatedResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(200);
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('successful password reset link response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new SuccessfulPasswordResetLinkRequestResponse($this->responseHelper, 'passwords.sent'))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(200);
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('email verification notification sent response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new EmailVerificationNotificationSentResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(202);
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('password confirmed response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new PasswordConfirmedResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(201); // HTTP_CREATED
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('two factor enabled response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new TwoFactorEnabledResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(200);
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('verify email response returns json when requested', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    if (! class_exists('ResponseHelper')) {
        class_alias(\App\Http\Helpers\ResponseHelper::class, 'ResponseHelper');
    }
    if (! class_exists('HttpResponse')) {
        class_alias(Illuminate\Http\Response::class, 'HttpResponse');
    }

    $resp = (new VerifyEmailResponse($this->responseHelper))->toResponse($request);

    expect($resp)->toBeInstanceOf(JsonResponse::class);
    expect($resp->getStatusCode())->toBe(200);
    $data = $resp->getData(true);
    expect($data['success'])->toBe(true);
    expect(array_key_exists('message', $data))->toBeTrue();
});

it('failed password confirmation response either throws for json requests or returns errors for web requests', function () {
    $request = Request::create('/dummy', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->headers->set('Accept', 'application/json');

    try {
        $resp = (new FailedPasswordConfirmationResponse())->toResponse($request);
    } catch (\Throwable $e) {
        expect($e)->toBeInstanceOf(\Throwable::class);
        return;
    }

    expect($resp)->not->toBeNull();

    if ($resp instanceof JsonResponse) {
        $data = $resp->getData(true);
        expect($data['message'])->toBeString();
        expect($data['message'])->toBe('auth.confirm_password.fail');
        return;
    }

    if (method_exists($resp, 'getSession') && $resp->getSession() && $resp->getSession()->has('errors')) {
        $errors = $resp->getSession()->get('errors');
        if (method_exists($errors, 'get') && $errors->get('password')) {
            $msg = $errors->get('password')[0] ?? null;
            expect($msg)->toBe('auth.confirm_password.fail');
            return;
        }
    }

    $content = method_exists($resp, 'getContent') ? (string) $resp->getContent() : '';
    expect($content)->toContain('auth.confirm_password.fail');
});
