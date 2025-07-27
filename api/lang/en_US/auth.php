<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'login' => [
        'success' => ":username logged in successfully",
        'already_logged_in' => "You are already logged in as :username",
        'fail' => 'Login attempt failed. Please check your credentials and try again.',
        'error' => [
            'title' => 'Login error',
            'message' => 'Failed to login :username. Please try again.'
        ],
        '2fa' => [
            'confirmed' => '2FA code confirmed successfully',
            'enabled' => '2FA code enabled successfully',
            'disabled' => '2FA code disabled successfully',
            'success' => '2FA code verified successfully',
            'fail' => '2FA code verification failed. Please try again.'
        ],
    ],
    'logout' => [
        'success' => 'Logged out successfully',
        'fail' => 'Logout attempt failed. Please try again.',
        'error' => [
            'title' => 'Logout error',
            'message' => 'Failed to logout. Please try again.'
        ],
    ],
    'confirm_password' => [
        'success' => 'Password confirmed successfully',
        'fail' => 'Password confirmation failed. Please try again.',
        'error' => [
            'title' => 'Password confirmation error',
            'message' => 'Failed to confirm password. Please try again.'
        ],
    ],
    'register' => [
        'success' => ':username registered successfully',
        'fail' => 'Registration attempt failed. Please try again.',
        'username' => [
            'invalid_characters' => 'The username contains invalid characters. Only alphanumeric characters, dashes, and underscores are allowed.',
            'whitespace' => 'The username cannot be only whitespace.'
        ],
        'error' => [
            'title' => 'Registration error',
            'message' => 'Failed to register :username. Please try again.'
        ],
        'verification' => [
            'sent' => 'Verification email sent successfully'
        ],
    ],
    'password_reset' => [
        'success' => 'Password reset successfully',
        'fail' => 'Password reset failed. Please try again.',
        'error' => [
            'title' => 'Password reset error',
            'message' => 'Failed to reset password. Please try again.'
        ]
    ],
    'password_update' => [
        'success' => 'Password updated successfully',
        'fail' => 'Password update failed. Please try again.',
        'error' => [
            'title' => 'Password update error',
            'message' => 'Failed to update password. Please try again.'
        ]
    ],
    'recovery_codes' => [
        'generated' => 'Recovery codes generated successfully',
        'error' => [
            'title' => 'Error generating recovery codes',
            'message' => 'Failed to generate recovery codes. Please try again.'
        ],
        'fail' => 'Recovery code generation failed. Please try again.',
    ],
    'browser_sessions' => [
        'retrieved' => 'Browser sessions retrieved successfully',
        'error' => [
            'title' => 'Error retrieving browser sessions',
            'message' => 'Failed to retrieve browser sessions. Please try again.'
        ],
        'logout' => [
            'success' => 'Other browser sessions logged out successfully',
            'fail' => 'Failed to log out other browser sessions. Please try again.',
            'error' => [
                'title' => 'Error logging out other browser sessions',
                'message' => 'Failed to log out other browser sessions. Please try again.'
            ]
        ],
        'session_error' => [
            'title' => 'Session error',
            'message' => [
                'not_configured' => 'Session storage not configured for this operation.',
                'unavailable' => 'Sessions unavailable. Please check your session configuration.',
                'not_found' => 'Session not found. Please check your session ID.'
            ]
        ]
    ],
];
