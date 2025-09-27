<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('app.frontend_url', 'http://frontend.test');
});

it('returns validation error when credentials are missing (guest JSON request)', function () {
    $response = $this->post('/api/login', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422);
});

it('returns user payload and redirect when valid credentials provided', function () {
    $password = 'Password123!';
    $user = User::factory()->create([
        'name' => 'Login Test',
        'username' => 'logintest',
        'email' => 'login@example.com',
        'password' => $password,
    ]);

    $response = $this->post('/api/login', [
        'email' => 'login@example.com',
        'password' => $password,
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => [
                'id', 'name', 'username', 'email', 'created_at', 'updated_at',
            ],
            'two_factor',
            'redirect_url',
        ]);

    expect($response->json('user.username'))->toBe('logintest');
    expect($response->json('redirect_url'))->not->toBeNull();
});
