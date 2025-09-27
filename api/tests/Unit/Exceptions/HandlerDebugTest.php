<?php

use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Http\Request;

uses(TestCase::class);

beforeEach(function () {
    $this->handler = app(Handler::class);
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
