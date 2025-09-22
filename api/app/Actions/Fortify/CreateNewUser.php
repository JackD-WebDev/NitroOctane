<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string|null>  $input
     */
    public function create(array $input): User
    {
        $locales = implode(',', config('app.supported_locales', ['en_US', 'es_US', 'fr_US', 'tl_US']));

        Validator::make($input, [
            'firstname' => 'required|string|alpha_dash|min:2|max:50',
            'middlename' => 'sometimes|nullable|string|alpha_dash|max:50',
            'lastname' => 'required|string|alpha_dash|min:2|max:50',
            'username' => 'required|string|min:3|max:50|unique:users',
            'email' => 'required|string|email|min:5|max:320|unique:users',
            'password' => $this->passwordRules(),
            'lang' => 'sometimes|string|in:' . $locales,
        ])->validate();

        $name = implode(' ', array_filter([
            $input['firstname'] ?? '',
            $input['middlename'] ?? '',
            $input['lastname'] ?? '',
        ]));

        return User::create([
            'name' => $name,
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'lang' => $input['lang'] ?? 'en_US',
        ]);
    }
}
