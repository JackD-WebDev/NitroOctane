<?php

use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Config;

uses(Tests\TestCase::class);

it('renders the password reset mailable with token email and frontend', function () {
    Config::set('app.frontend_url', 'http://test-frontend.local');

    $token = 'render-token-abc';
    $email = 'user@example.test';

    $mailable = new PasswordResetEmail($token, $email);

    $rendered = $mailable->render();

    expect($rendered)->toContain(urlencode($token));
    expect($rendered)->toContain(urlencode($email));
    expect($rendered)->toContain('http://test-frontend.local');
});
