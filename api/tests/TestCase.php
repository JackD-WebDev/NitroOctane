<?php

namespace Tests;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.default' => env('MAIL_MAILER', 'array')]);
        config(['queue.default' => env('QUEUE_CONNECTION', 'sync')]);

        Mail::fake();
        Notification::fake();
        Queue::fake();
    }

    /**
     * Ensure the given route URI has the 'web' middleware so the session store
     * is available when tests exercise named throttle limiters that rely on
     * session()->get('login.id'). This mutates the in-memory route action only
     * for the duration of the test process.
     *
     * @param  string  $uri  Route URI to patch (e.g. '/api/two-factor-challenge')
     */
    protected function enableRouteSession(string $uri): void
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        foreach ($routes as $route) {
            $routeUri = $route->uri();
            if ($routeUri === ltrim($uri, '/') || $routeUri === $uri) {
                $action = $route->getAction();
                $middlewares = $action['middleware'] ?? [];
                $middlewares = is_array($middlewares) ? $middlewares : explode(',', $middlewares);
                $startSession = \Illuminate\Session\Middleware\StartSession::class;
                if (! in_array($startSession, $middlewares, true)) {
                    $middlewares[] = $startSession;
                    $action['middleware'] = $middlewares;
                    $route->setAction($action);
                }
            }
        }
    }
}
