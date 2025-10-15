<?php

use Tests\TestCase;
use App\Mail\PasswordResetEmail;

uses(TestCase::class);

it('provides envelope subject and view data', function () {
    $token = 'token-xyz';
    $email = 'someone@example.com';

    $mailable = new PasswordResetEmail($token, $email);

    $envelope = $mailable->envelope();
    $content = $mailable->content();

    expect($envelope->subject)->toContain('Password Reset');
    expect($content->with['token'])->toBe($token);
    expect($content->with['email'])->toBe($email);
    expect($content->markdown)->toBe('emails.password-reset');
});
