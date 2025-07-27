<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the user controller to build
    | the user related messages. You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    'found' => [
        'username' => 'User :username retrieved successfully.',
        'id' => 'User with ID :user_id retrieved successfully.',
        'email' => 'User with email :email retrieved successfully.',
        'default' => 'User information retrieved successfully.',
        'collection' => 'Requested users retrieved successfully.'
    ],
    'created' => 'User created successfully.',
    'updated' => 'User updated successfully.',
    'deleted' => 'User deleted successfully.',
    'not_found' => [
        'title' => 'User not found.',
        'message' => [
            'name' => 'User :name not found.',
            'username' => 'User :username not found.',
            'id' => 'User with ID :user_id not found.',
            'email' => 'User with email :email not found.'
        ]
    ],
    'not_defined' => 'User not defined.',
    'malformed' => 'Malformed user data.'

];