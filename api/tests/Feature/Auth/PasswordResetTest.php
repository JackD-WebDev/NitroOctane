<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use App\Notifications\QueuedResetPassword;
use App\Events\PasswordChanged;
use App\Events\SessionLoggedOut;

beforeEach(function () {
    Notification::fake();
});

it('sends a password reset link for a valid email', function () {
    $user = User::factory()->create(['email' => 'pwreset@example.com']);

    $response = $this->postJson('/api/forgot-password', ['email' => 'pwreset@example.com']);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'WE HAVE EMAILED YOUR PASSWORD RESET LINK.']);

    Notification::assertSentTo($user, QueuedResetPassword::class);
});

it('returns validation error for unknown email', function () {
    $response = $this->postJson('/api/forgot-password', ['email' => 'unknown@example.com']);

    $response->assertStatus(422)
        ->assertJsonStructure(['errors']);
});

it('resets the password with a valid token', function () {
    $user = User::factory()->create(['email' => 'reset-token@example.com']);

    Event::fake();

    // Simulate generating a token by sending the notification and capturing the token from the notification
    $this->postJson('/api/forgot-password', ['email' => 'reset-token@example.com']);

    Notification::assertSentTo($user, QueuedResetPassword::class, function ($notif) use ($user, &$token) {
        $token = $notif->token ?? null;
        return true;
    });

    // If token not captured, fail early to avoid false positives
    expect(isset($token))->toBeTrue();

    $response = $this->postJson('/api/reset-password', [
        'email' => 'reset-token@example.com',
        'token' => $token,
        // application requires 12+ characters for passwords in tests
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(200);

    // message text varies by locale; ensure it mentions "reset"
    $body = $response->json();
    $this->assertStringContainsStringIgnoringCase('reset', $body['message'] ?? '');

    // Ensure PasswordChanged and SessionLoggedOut events were broadcast
    Event::assertDispatched(PasswordChanged::class, function ($e) use ($user) {
        return $e->user->id === $user->id;
    });

    Event::assertDispatched(SessionLoggedOut::class, function ($e) use ($user) {
        return $e->user->id === $user->id;
    });
});
