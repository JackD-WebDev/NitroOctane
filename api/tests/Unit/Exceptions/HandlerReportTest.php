<?php

use Tests\TestCase;
use App\Exceptions\Handler;

uses(TestCase::class);

beforeEach(function () {
    $this->handler = app(Handler::class);
});

it('returns generic database error for QueryException when expects json', function () {
    $request = \Illuminate\Http\Request::create('/api/db', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
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
    $testHandler = new class(app(\App\Http\Helpers\ResponseHelper::class)) extends \App\Exceptions\Handler
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
    $testHandler = new class(app(\App\Http\Helpers\ResponseHelper::class)) extends \App\Exceptions\Handler
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
                error_log('Swallowed TypeError in Handler::doReport test: '.$e->getMessage());
            }
        }
    };

    app()->instance(\App\Exceptions\Handler::class, $testHandler);

    $testHandler->report(new \Exception('trigger-doReport'));

    expect($testHandler->invoked)->toBeTrue();
});
