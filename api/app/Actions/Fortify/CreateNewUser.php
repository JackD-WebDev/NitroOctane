<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Validation\Rule;
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
        Validator::make($input, [
            'firstname' => 'required|string|alpha_dash|min:2|max:50',
            'middlename' => 'sometimes|nullable|string|alpha_dash|max:50',
            'lastname' => 'required|string|alpha_dash|min:2|max:50',
            'username' => 'required|string|min:3|max:50|unique:users',
            'email' => 'required|string|email|min:5|max:320|unique:users',
            'password' => $this->passwordRules(),
        ])->validate();

        $name = implode(' ', array_filter([
            $input['firstname'] ?? '',
            $input['middlename'] ?? '',
            $input['lastname'] ?? ''
        ]));

        return User::create([
            'name' => $name,
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
