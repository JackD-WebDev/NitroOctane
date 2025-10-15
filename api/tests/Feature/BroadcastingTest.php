<?php

namespace Tests\Feature;

use App\Models\User;
use App\Events\PasswordChanged;
use App\Events\SessionLoggedOut;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\InteractsWithBroadcasting;

it('broadcasts events when password is reset', function () {
    Event::fake();
    $user = User::factory()->create([
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $token = app('auth.password.broker')->createToken($user);

    $response = $this->post('/api/reset-password', [
        'email' => 'testuser@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Your password has been reset.',
        ]);

    $this->assertEventBroadcasted(PasswordChanged::class, function (PasswordChanged $event) use ($user) {
        return $event->user->id === $user->id;
    });

    $this->assertEventBroadcasted(SessionLoggedOut::class, function (SessionLoggedOut $event) use ($user) {
        return $event->user->id === $user->id;
    });
})->uses(InteractsWithBroadcasting::class);
