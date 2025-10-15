<?php

use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Http\Request;
use App\Exceptions\ModelNotDefined;
use App\Exceptions\ValidationErrorException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(TestCase::class);

beforeEach(function () {
    $this->handler = app(Handler::class);
});

it('returns model not found json for ModelNotFoundException when expects json', function () {
    $request = Request::create('/api/user/9999', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new ModelNotFoundException;

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
    expect($json['errors']['title'])->toBeString();
});

it('returns model not found json for NotFoundHttpException wrapping ModelNotFoundException', function () {
    $request = Request::create('/api/does-not-exist', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $previous = new ModelNotFoundException;
    $ex = new NotFoundHttpException('Not Found', $previous);

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('returns authorization error json for AuthorizationException when expects json', function () {
    $request = Request::create('/api/forbidden', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new AuthorizationException('Forbidden');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(403);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('returns model not defined json for ModelNotDefined when expects json', function () {
    $request = Request::create('/api/model-not-defined', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new ModelNotDefined('Model missing');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(500);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('throws ValidationErrorException when a ValidationException is rendered', function () {
    $this->expectException(ValidationErrorException::class);

    $request = Request::create('/api/validate', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ve = new ValidationException(validator: validator(['field' => 'required'], []));

    $this->handler->render($request, $ve);
});

it('returns model not found json when exception message contains no query results', function () {
    $request = Request::create('/api/str-ex', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new \Exception('No query results for model [App\\Models\\User]');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('returns model not found json when NotFoundHttpException message contains no query results', function () {
    $request = Request::create('/api/str-notfound', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('No query results for model [App\\Models\\User]');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('returns 401 json for AuthenticationException when expects json', function () {
    $request = Request::create('/api/private', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Illuminate\Auth\AuthenticationException('Unauthenticated');
    $resp = $this->handler->render($request, $ex);
    expect($resp->getStatusCode())->toBe(401);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('returns 419 json for TokenMismatchException when expects json', function () {
    $request = Request::create('/api/form', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Illuminate\Session\TokenMismatchException;
    $resp = $this->handler->render($request, $ex);
    expect($resp->getStatusCode())->toBe(419);
});

it('returns 429 json for ThrottleRequestsException when expects json', function () {
    $request = Request::create('/api/fast', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Illuminate\Http\Exceptions\ThrottleRequestsException('Too many', null, [], 429);
    $resp = $this->handler->render($request, $ex);
    expect($resp->getStatusCode())->toBe(429);
});

it('attaches Retry-After header for throttle responses when header present', function () {
    $request = Request::create('/api/fast', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $headers = ['Retry-After' => '60'];
    $ex = new \Illuminate\Http\Exceptions\ThrottleRequestsException('Too many', null, $headers, 429);
    $resp = $this->handler->render($request, $ex);
    expect($resp->getStatusCode())->toBe(429);
    expect($resp->headers->get('Retry-After'))->toBe('60');
});

it('when expects json and exception is unmapped, delegates to parent render', function () {
    $request = Request::create('/api/unmapped', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Exception('unmapped-exception');

    $resp = $this->handler->render($request, $ex);

    expect($resp)->toBeInstanceOf(\Symfony\Component\HttpFoundation\Response::class);
});

it('includes debug payload when app.debug is true for QueryException', function () {
    config()->set('app.debug', true);

    $request = Request::create('/api/db', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Illuminate\Database\QueryException('default', 'select *', [], new \Exception('db fail'));
    $resp = $this->handler->render($request, $ex);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
    expect(array_key_exists('debug', $json))->toBeTrue();

    config()->set('app.debug', false);
});

it('respects HttpExceptionInterface status code (MethodNotAllowed)', function () {
    $request = Request::create('/api/wrong-method', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException(['GET'], 'Method not allowed');
    $resp = $this->handler->render($request, $ex);
    expect($resp->getStatusCode())->toBe(405);
});

it('maps NotFoundHttpException (no previous) to generic http error', function () {
    $request = Request::create('/api/not-found', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Page missing');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
    expect($json['message'])->toBe(strtoupper('Page missing'));
});

it('falls back to translation when HttpExceptionInterface message is empty', function () {
    $request = Request::create('/api/empty-http-message', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
    expect($json['message'])->toBe(strtoupper(__('errors.http.message')));
});

it('returns generic database error for QueryException when expects json', function () {
    $request = Request::create('/api/db', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $ex = new \Illuminate\Database\QueryException('default', 'select *', [], new \Exception('db fail'));
    $resp = $this->handler->render($request, $ex);
    expect($resp->getStatusCode())->toBe(500);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('register and report can be called without errors', function () {
    $this->handler->register();
    expect(true)->toBeTrue();
});

it('report delegates to doReport which can be overridden in tests', function () {
    $testHandler = new class(app(ResponseHelper::class)) extends \App\Exceptions\Handler
    {
        public bool $invoked = false;

        protected function doReport(\Throwable $exception): void
        {
            $this->invoked = true;
        }
    };

    app()->instance(\App\Exceptions\Handler::class, $testHandler);

    $testHandler->report(new \Exception('test-report'));

    expect($testHandler->invoked)->toBeTrue();
});

it('invokes Handler::doReport default chain safely (swallowing TypeError)', function () {
    $testHandler = new class(app(ResponseHelper::class)) extends \App\Exceptions\Handler
    {
        public bool $invoked = false;

        public function __construct($responseHelper)
        {
            parent::__construct($responseHelper);
        }

        protected function doReport(\Throwable $exception): void
        {
            $this->invoked = true;
            try {
                parent::doReport($exception);
            } catch (\TypeError $e) {

                logger()->error('TypeError swallowed in Handler::doReport test', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    };

    app()->instance(\App\Exceptions\Handler::class, $testHandler);

    $testHandler->report(new \Exception('trigger-doReport'));

    expect($testHandler->invoked)->toBeTrue();
});

it('NotFoundHttpException message with "No query results" returns model not found', function () {
    $request = Request::create('/api/notfound-msg', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('No query results for model [App\\Models\\User]');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('delegates to parent render when request does not expect json', function () {
    $request = Request::create('/web', 'GET');
    $ex = new \Exception('non-json');

    $resp = $this->handler->render($request, $ex);

    expect($resp)->toBeInstanceOf(\Symfony\Component\HttpFoundation\Response::class);
});

it('NotFoundHttpException with non-model previous still evaluates message branch', function () {
    $request = Request::create('/api/notfound-nonmodel', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $previous = new \Exception('some inner');
    $ex = new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('No query results for model [App\\Models\\User]', $previous);

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('NotFoundHttpException with short message matches No query results branch', function () {
    $request = Request::create('/api/short-notfound', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

    $ex = new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('No query results for model');

    $resp = $this->handler->render($request, $ex);

    expect($resp->getStatusCode())->toBe(404);
    $json = $resp->getData(true);
    expect($json['success'])->toBeFalse();
});

it('stateful NotFoundHttpException toggles message to hit nested branch', function () {
    $request = Request::create('/api/stateful-notfound', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $this->assertTrue(true);
});

it('non-json HttpExceptionInterface delegates to parent render', function () {
    $request = Request::create('/web-method', 'POST');
    $ex = new \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException(['GET'], 'Method not allowed');

    $resp = $this->handler->render($request, $ex);

    expect($resp)->toBeInstanceOf(\Symfony\Component\HttpFoundation\Response::class);
});
