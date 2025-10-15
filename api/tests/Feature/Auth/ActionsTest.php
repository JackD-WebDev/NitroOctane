<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Validation\ValidationException;
use App\Actions\Fortify\UpdateUserProfileInformation;

it('updates user password with valid input', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    $updateUserPassword->update($user, $input);

    $user->refresh();
    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
});

it('throws validation exception when current password is missing', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when current password is incorrect', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $this->actingAs($user);

    $input = [
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password is missing', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when password confirmation does not match', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'DifferentPassword123!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password is too short', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'Short1!',
        'password_confirmation' => 'Short1!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password lacks mixed case', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'newpassword123!',
        'password_confirmation' => 'newpassword123!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password lacks numbers', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPasswordOnly!',
        'password_confirmation' => 'NewPasswordOnly!',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when new password lacks symbols', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    $input = [
        'current_password' => 'CurrentPassword123!',
        'password' => 'NewPassword123',
        'password_confirmation' => 'NewPassword123',
    ];

    expect(fn () => $updateUserPassword->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('does not change password when validation fails', function () {

    $updateUserPassword = new UpdateUserPassword;
    $user = User::factory()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);
    $originalPassword = $user->password;

    $this->actingAs($user);

    $input = [
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ];

    try {
        $updateUserPassword->update($user, $input);
    } catch (ValidationException $e) {
    }

    $user->refresh();
    expect($user->password)->toBe($originalPassword);
});

it('updates user profile information with valid input', function () {

    $updateProfile = new UpdateUserProfileInformation;
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
    expect($user->email_verified_at)->toBeNull();
});

it('updates profile without changing email verification when email unchanged', function () {
    $updateProfile = new UpdateUserProfileInformation;
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
        'email' => 'same@example.com',
    ];

    $updateProfile->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('newname');
    expect($user->username)->toBe('newusername');
    expect($user->email)->toBe('same@example.com');
    expect($user->email_verified_at)->not->toBeNull();
});

it('throws validation exception when name is missing', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create();

    $input = [
        'username' => 'newusername',
        'email' => 'new@example.com',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when username is missing', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'email' => 'new@example.com',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when email is missing', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when name is too short', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create();

    $input = [
        'name' => 'x',
        'username' => 'newusername',
        'email' => 'new@example.com',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when username is too short', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'username' => 'x',
        'email' => 'new@example.com',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when email is invalid', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create();

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
        'email' => 'invalid-email',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when username is taken by another user', function () {
    // Mail fake provided globally

    $updateProfile = new UpdateUserProfileInformation;
    $existingUser = User::factory()->create(['username' => 'takenusername']);
    $user = User::factory()->create(['username' => 'originalusername']);

    $input = [
        'name' => 'newname',
        'username' => 'takenusername',
        'email' => 'new@example.com',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('throws validation exception when email is taken by another user', function () {

    $updateProfile = new UpdateUserProfileInformation;
    $existingUser = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'original@example.com']);

    $input = [
        'name' => 'newname',
        'username' => 'newusername',
        'email' => 'taken@example.com',
    ];

    expect(fn () => $updateProfile->update($user, $input))
        ->toThrow(ValidationException::class);
});

it('allows user to keep their current username and email', function () {
    $updateProfile = new UpdateUserProfileInformation;
    $user = User::factory()->create([
        'name' => 'oldname',
        'username' => 'sameusername',
        'email' => 'same@example.com',
    ]);

    $input = [
        'name' => 'newname',
        'username' => 'sameusername',
        'email' => 'same@example.com',
    ];

    $updateProfile->update($user, $input);

    $user->refresh();
    expect($user->name)->toBe('newname');
    expect($user->username)->toBe('sameusername');
    expect($user->email)->toBe('same@example.com');
});
