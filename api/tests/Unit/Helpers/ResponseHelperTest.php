<?php

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\ResponseHelper;

uses(TestCase::class);

it('returns service unavailable when health table not healthy', function () {
    $helper = new ResponseHelper;

    DB::shouldReceive('table->value')->with('health')->andReturn(0);

    $resp = $helper->healthCheckResponse();

    expect($resp->getStatusCode())->toBe(503);
    $data = $resp->getData(true);
    expect($data['success'])->toBeFalse();
});

it('includes debug when _debug provided and app.debug true', function () {
    config(['app.debug' => true]);

    $helper = new ResponseHelper;

    $resp = $helper->errorResponse('title', 'message', ['_debug' => ['foo' => 'bar']], 500);

    $data = $resp->getData(true);
    expect(isset($data['debug']))->toBeTrue();
    expect($data['debug']['foo'])->toBe('bar');
});

it('returns ok when health table healthy', function () {
    $helper = new ResponseHelper;

    DB::shouldReceive('table->value')->with('health')->andReturn(1);

    $resp = $helper->healthCheckResponse();

    expect($resp->getStatusCode())->toBe(200);
    $data = $resp->getData(true);
    expect($data['success'])->toBeTrue();
});

it('requestResponse accepts a Model and converts to array', function () {
    $user = \App\Models\User::factory()->create();
    $helper = new ResponseHelper;

    $resp = $helper->requestResponse($user, 'ok', true, 200);

    $data = $resp->getData(true);
    expect($data['success'])->toBeTrue();
    expect($data['username'])->toBe($user->username);
});

it('attaches provided headers to errorResponse', function () {
    $helper = new ResponseHelper;

    $resp = $helper->errorResponse('t', 'm', [], 429, ['Retry-After' => '10']);

    expect($resp->headers->get('Retry-After'))->toBe('10');
});

it('does not include debug when _debug provided and app.debug false', function () {
    config(['app.debug' => false]);

    $helper = new ResponseHelper;

    $resp = $helper->errorResponse('title', 'message', ['_debug' => ['foo' => 'bar']], 500);

    $data = $resp->getData(true);
    expect(isset($data['debug']))->toBeFalse();
});

it('resourceResponse builds response from JsonResource with links and meta', function () {
    $resource = new class(['name' => 'alice']) extends \Illuminate\Http\Resources\Json\JsonResource
    {
        public function response($request = null)
        {
            return response()->json([
                'data' => $this->resource,
                'links' => ['self' => '/users/1'],
                'meta' => ['count' => 1],
            ]);
        }
    };

    config(['app.full_name' => 'NitroOctane']);

    $helper = new ResponseHelper;

    $resp = $helper->resourceResponse($resource, 'ok', true, 200);

    $data = $resp->getData(true);
    expect($data['success'])->toBeTrue();
    expect($data['data']['name'])->toBe('alice');
    expect($data['links']['self'])->toBe('/users/1');
    expect($data['meta']['count'])->toBe(1);
    expect($data['version'])->toBe('NitroOctane');
});

it('requestResponse accepts object with toArray method', function () {
    $obj = new class
    {
        public function toArray()
        {
            return ['foo' => 'bar'];
        }
    };

    $helper = new ResponseHelper;

    $resp = $helper->requestResponse($obj, 'ok', true, 200);

    $data = $resp->getData(true);
    expect($data['success'])->toBeTrue();
    expect($data['foo'])->toBe('bar');
});

it('requestResponse casts plain object to array if no toArray', function () {
    $obj = new \stdClass;
    $obj->a = 1;
    $obj->b = 'two';

    $helper = new ResponseHelper;

    $resp = $helper->requestResponse($obj, 'ok', true, 200);

    $data = $resp->getData(true);
    expect($data['success'])->toBeTrue();
    expect($data['a'])->toBe(1);
    expect($data['b'])->toBe('two');
});
