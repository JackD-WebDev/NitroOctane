<?php

/** @noinspection PhpUnusedAliasInspection */

namespace Tests\Feature;

use App\Models\User;
use App\Events\PasswordChanged;
use App\Events\SessionLoggedOut;
use Tests\Concerns\InteractsWithBroadcasting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns the correct JSON structure after successful registration', function () {

    $response = $this->post('/api/register', [
        'firstname' => 'Test',
        'middlename' => 'Middle',
        'lastname' => 'User',
        'username' => 'testuser1',
        'lang' => 'en_US',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertJsonFragment([
        'message' => 'TESTUSER1 REGISTERED SUCCESSFULLY',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => [
                'id',
                'name',
                'username',
                'preferred_language',
                'email',
                'created_at',
                'updated_at',
            ],
            'redirect_url',
        ]);
});

it('returns an error for missing registration fields', function () {
    $response = $this->post('/api/register', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'The firstname field is required. (and 4 more errors)',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['firstname', 'lastname', 'username', 'email', 'password'],
        ]);
});

it('returns an error for duplicate registration', function () {
    
    User::factory()->create([
        'email' => 'testuser@example.com',
    ]);

    $response = $this->post('/api/register', [
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'The firstname field is required. (and 2 more errors)',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['email', 'firstname', 'lastname'],
        ]);
});

it('returns the correct JSON structure after successful login', function () {
    $user = User::factory()->create([
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->post('/api/login', [
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'TESTUSER1 LOGGED IN SUCCESSFULLY',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => [
                'id',
                'name',
                'username',
                'email',
                'created_at',
                'updated_at',
            ],
            'two_factor',
            'redirect_url',
        ]);
});

it('returns the correct JSON structure after successful login with 2FA', function () {
    $user = User::factory()->create([
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->post('/api/login', [
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'TESTUSER1 LOGGED IN SUCCESSFULLY',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => [
                'id',
                'name',
                'username',
                'email',
                'created_at',
                'updated_at',
            ],
            'two_factor',
            'redirect_url',
        ]);
});

it('returns an error for missing login credentials', function () {
    $response = $this->post('/api/login', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'The email field is required. (and 1 more error)',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['email', 'password'],
        ]);
});

it('returns an error for invalid login credentials', function () {
    $response = $this->post('/api/login', [
        'email' => 'invalid@example.com',
        'password' => 'InvalidPassword!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'These credentials do not match our records.',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['email'],
        ]);
});

it('returns an error for unauthenticated user', function () {
    $response = $this->get('/api/user', [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

it('returns user data for authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->actingAs($user)->get('/api/user', [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'USER TESTUSER1 RETRIEVED SUCCESSFULLY.',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'type',
                'user_id',
                'has2FA',
                'attributes' => [
                    'username',
                    'email',
                    'created_at_dates' => [
                        'created_at_human',
                        'created_at',
                    ],
                    'updated_at_dates' => [
                        'updated_at_human',
                        'updated_at',
                    ],
                ],
            ],
            'links',
            'meta',
            'version',
        ]);
});

it('returns 404 for non-existent user', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/api/user/9999', [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => 'MODEL NOT FOUND.',
            ],
        ]);
});

it('returns the correct JSON structure after successful password reset', function () {
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
});

it('enforces rate limiting after multiple failed login attempts', function () {
    $email = 'ratelimit@example.com';
    $user = User::factory()->create([
        'email' => $email,
        'password' => 'Password123!',
    ]);

    for ($i = 0; $i < 6; $i++) {
        $this->post('/api/login', [
            'email' => $email,
            'password' => 'WrongPassword!',
        ], [
            'Accept' => 'application/json',
        ]);
    }

    $response = $this->post('/api/login', [
        'email' => $email,
        'password' => 'WrongPassword!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(429)
        ->assertJson([
            'message' => 'Too Many Attempts.',
        ]);
    $retry = $response->headers->get('Retry-After');
    $this->assertNotNull($retry, 'Retry-After header should be present on 429 responses');
    $this->assertTrue(is_numeric($retry) || ctype_digit((string) $retry));
});

it('enforces two-factor rate limiting and includes Retry-After header', function () {
    $user = User::factory()->create([
        'email' => '2fa@example.com',
        'password' => 'Password123!',
    ]);

    $this->post('/api/login', [
        'email' => '2fa@example.com',
        'password' => 'Password123!',
    ], [
        'Accept' => 'application/json',
    ]);

    $this->startSession();
    session(['login.id' => $user->id]);

    $this->enableRouteSession('/api/two-factor-challenge');

    for ($i = 0; $i < 6; $i++) {
        $this->withSession(['login.id' => $user->id])->post('/api/two-factor-challenge', [
            'code' => '000000',
        ], [
            'Accept' => 'application/json',
        ]);
    }

    $response = $this->withSession(['login.id' => $user->id])->post('/api/two-factor-challenge', [
        'code' => '000000',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(429)
        ->assertJson([
            'message' => 'Too Many Attempts.',
        ]);

    $retry = $response->headers->get('Retry-After');
    $this->assertNotNull($retry, 'Retry-After header should be present on two-factor 429 responses');
    $this->assertTrue(is_numeric($retry) || ctype_digit((string) $retry));
});

it('returns the correct JSON structure after successful logout', function () {
    $user = User::factory()->create([
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->actingAs($user)->post('/api/logout', [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'LOGGED OUT SUCCESSFULLY',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'redirect_url',
        ]);
});


it('returns the user collection with the correct structure from the users endpoint', function () {
    $user = User::factory()->create();
    User::factory()->count(2)->create(); 

    $response = $this->actingAs($user)->get('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'data' => [
                        'type',
                        'user_id',
                        'attributes'
                    ],
                    'links',
                    'meta'
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    $data = $response->json();
    expect($data['success'])->toBe(true);
    expect(count($data['data']))->toBeGreaterThan(0);
});
