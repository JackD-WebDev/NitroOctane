<?php

use App\Http\Controllers\Users\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

it('returns 401 for getMe when unauthenticated', function () {
    $controller = app(UserController::class);

    // Ensure no user is authenticated
    auth()->logout();

    $response = $controller->getMe();

    expect($response->getStatusCode())->toBe(401);
    $data = $response->getData(true);
    expect($data['success'])->toBeFalse();
});

it('returns user payload for getMe when authenticated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Provide a request in the container so resources can build links/meta
    $req = Request::create('/', 'GET');
    app()->instance('request', $req);

    $controller = app(UserController::class);

    $response = $controller->getMe();

    expect($response->getStatusCode())->toBe(200);
    $data = $response->getData(true);

    expect($data['success'])->toBeTrue();
    // Ensure the returned user_id matches the authenticated user's id
    expect($data['data']['user_id'] ?? null)->toBe($user->id);
});

it('returns 404 json when findById does not exist', function () {
    $controller = app(UserController::class);

    $request = Request::create('/users/9999', 'GET', ['id' => 9999]);

    $response = $controller->findById($request);

    expect($response->getStatusCode())->toBe(404);
    $json = $response->getData(true);
    expect($json['errors']['message'])->toBe('MODEL NOT FOUND.');
});

it('returns null data when findByUsername not found', function () {
    $controller = app(UserController::class);

    $request = Request::create('/users/username', 'GET', ['username' => 'no-such-user']);

    $response = $controller->findByUsername($request);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json['data'])->toBeNull();
});

it('returns null data when findByEmail not found', function () {
    $controller = app(UserController::class);

    $request = Request::create('/users/email', 'GET', ['email' => 'nope@example.com']);

    $response = $controller->findByEmail($request);

    expect($response->getStatusCode())->toBe(200);
    $json = $response->getData(true);
    expect($json['data'])->toBeNull();
});
