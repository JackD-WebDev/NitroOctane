<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Providers\FortifyServiceProvider;

uses(TestCase::class);

it('executes the fortify rate limiter closures for login and two-factor', function () {
    $app = $this->app;
    $app->register(FortifyServiceProvider::class);
    $app->boot();

    $req = Request::create('/', 'POST', ['email' => 'foo@example.com']);
    $req->server->set('REMOTE_ADDR', '127.0.0.1');

    $loginLimiter = RateLimiter::limiter('login');
    expect(is_callable($loginLimiter))->toBeTrue();

    $twoFactorLimiter = RateLimiter::limiter('two-factor');
    expect(is_callable($twoFactorLimiter))->toBeTrue();

    $res1 = $loginLimiter($req);

    $req->setLaravelSession(app('session.store'));
    $res2 = $twoFactorLimiter($req);

    expect($res1)->not->toBeNull();
    expect($res2)->not->toBeNull();
});
