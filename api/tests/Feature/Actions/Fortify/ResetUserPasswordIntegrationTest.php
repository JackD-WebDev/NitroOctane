<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Support\Facades\Notification;

it('updates the database and notifies the user using the sqlite in-memory database', function () {
    Notification::fake();

    $user = User::factory()->create();

    $action = new ResetUserPassword;

    $action->reset($user, ['password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!']);

    $fresh = User::find($user->id);

    expect($fresh)->not->toBeNull();
    expect(Hash::check('NewPassword123!', $fresh->password))->toBeTrue();

    Notification::assertSentTo($fresh, \App\Notifications\QueuedPasswordChanged::class);
});
