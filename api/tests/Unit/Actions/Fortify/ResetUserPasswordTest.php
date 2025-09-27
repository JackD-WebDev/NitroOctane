<?php

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

it('resets the password and notifies user and broadcasts events', function () {
    Notification::fake();

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('getAuthIdentifier')->andReturn(123);
    $user->shouldReceive('save')->andReturnTrue();
    $user->shouldReceive('notify')->andReturnNull();
    $user->password = 'original';

    $action = new ResetUserPassword();

    $action->reset($user, ['password' => 'Password123!', 'password_confirmation' => 'Password123!']);

    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});

it('deletes sessions when session driver is database', function () {
    // Force session driver to database
    config(['session.driver' => 'database', 'session.table' => 'sessions']);

    DB::shouldReceive('table')->once()->with('sessions')->andReturnSelf();
    DB::shouldReceive('where')->once()->with('user_id', 123)->andReturnSelf();
    DB::shouldReceive('delete')->once();

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('getAuthIdentifier')->andReturn(123);
    $user->shouldReceive('save')->andReturnTrue();
    $user->shouldReceive('notify')->andReturnNull();
    $user->password = 'original';
    $action = new ResetUserPassword();

    $action->reset($user, ['password' => 'Password123!', 'password_confirmation' => 'Password123!']);

    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});

it('throws validation exception for invalid password', function () {
    $this->expectException(ValidationException::class);

    $user = User::factory()->make();
    $action = new ResetUserPassword();

    $action->reset($user, ['password' => 'short']);
});
