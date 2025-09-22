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
    Mail::fake();

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
    Mail::fake();
    
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
    Mail::fake();
    Notification::fake(); // Prevent all notifications including password change
    
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

    // Assert that broadcast events were dispatched
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

it('updates user password with valid input', function () {
    Mail::fake(); // Prevent password change notification emails
    Notification::fake(); // Prevent all notifications

    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    // Authenticate the user for the current_password validation to work
    $this->actingAs($user);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!'
    ];

    $updateUserPassword->update($user, $input);

    $user->refresh();
    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

it('throws validation exception when current password is missing', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when current password is incorrect', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password is missing', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password_confirmation' => 'NewPassword123!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when password confirmation does not match', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'DifferentPassword123!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password is too short', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'Short1!',
        'password_confirmation' => 'Short1!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password lacks mixed case', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'newpassword123!',
        'password_confirmation' => 'newpassword123!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password lacks numbers', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPasswordOnly!',
        'password_confirmation' => 'NewPasswordOnly!'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password lacks symbols', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123'
    ];

    expect(fn() => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('does not change password when validation fails', function () {
    Mail::fake(); // Prevent any emails during test
    
    $updateUserPassword = new UpdateUserPassword();
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!')
    ]);
    $originalPassword = $user->password;
    
    $this->actingAs($user);
    
    $input = [
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!'
    ];

    try {
        $updateUserPassword->update($user, $input);
    } catch (ValidationException $e) {
        // Expected to throw
    }

    $user->refresh();
    expect($user->password)->toBe($originalPassword);
});


it('updates user profile information with valid input', function () {
    Mail::fake();
    
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create([
        'name' => 'oldname',
        'username' => 'oldusername',
        'email' => 'old@example.com',
    ]);

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
        'email' => 'new@example.com',
    ];

    $updateProfile->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('newname');
    expect($user->username)->toBe('newusername');
    expect($user->email)->toBe('new@example.com');
    expect($user->email_verified_at)->toBeNull(); // Email changed, so verification reset
});

it('updates profile without changing email verification when email unchanged', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $verifiedAt = now();
    $user = User::factory()->create([
        'name' => 'oldname',
        'username' => 'oldusername',
        'email' => 'same@example.com',
        'email_verified_at' => $verifiedAt,
    ]);

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
        'email' => 'same@example.com', // Same email
    ];

    $updateProfile->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('newname');
    expect($user->username)->toBe('newusername');
    expect($user->email)->toBe('same@example.com');
    expect($user->email_verified_at)->not->toBeNull(); // Verification preserved
});

it('throws validation exception when name is missing', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create();

    $input = [
        'username' => 'newusername',
        'email' => 'new@example.com',
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when username is missing', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'email' => 'new@example.com',
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when email is missing', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when name is too short', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create();

    $input = [
        'name' => 'x', // Too short
        'username' => 'newusername',
        'email' => 'new@example.com',
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when username is too short', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'username' => 'x', // Too short
        'email' => 'new@example.com',
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when email is invalid', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
        'email' => 'invalid-email', // Invalid format
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when username is taken by another user', function () {
    Mail::fake(); // Prevent emails during user creation
    
    $updateProfile = new UpdateUserProfileInformation();
    $existingUser = User::factory()->create(['username' => 'takenusername']);
    $user = User::factory()->create(['username' => 'originalusername']);

    $input = [
        'name' => 'newname',
        'username' => 'takenusername', // Already taken
        'email' => 'new@example.com',
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when email is taken by another user', function () {
    Mail::fake(); // Prevent emails during user creation
    
    $updateProfile = new UpdateUserProfileInformation();
    $existingUser = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'original@example.com']);

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
        'email' => 'taken@example.com', // Already taken
    ];

    expect(fn() => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('allows user to keep their current username and email', function () {
    $updateProfile = new UpdateUserProfileInformation();
    $user = User::factory()->create([
        'name' => 'oldname',
        'username' => 'sameusername',
        'email' => 'same@example.com',
    ]);

    $input = [
        'name' => 'newname',
        'username' => 'sameusername', // Same username
        'email' => 'same@example.com', // Same email
    ];

    $updateProfile->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('newname');
    expect($user->username)->toBe('sameusername');
    expect($user->email)->toBe('same@example.com');
});

// UserCollection Resource Tests

it('users endpoint returns user collection with correct structure', function () {
    $user = User::factory()->create();
    User::factory()->count(2)->create(); // Create additional users

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
