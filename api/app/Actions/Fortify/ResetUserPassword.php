<?php

namespace App\Actions\Fortify;

use DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->getAuthIdentifier())
                ->delete();
        }

        if (method_exists($user, 'notify')) {
            $user->notify(new \App\Notifications\QueuedPasswordChanged);
        }

        broadcast(new \App\Events\PasswordChanged($user))->toOthers();
        broadcast(new \App\Events\SessionLoggedOut($user))->toOthers();
    }
}
