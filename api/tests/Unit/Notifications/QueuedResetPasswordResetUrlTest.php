<?php

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use App\Notifications\QueuedResetPassword;

uses(TestCase::class);

class DummyNotifiable
{
    public function getEmailForPasswordReset()
    {
        return 'dummy@notifiable.test';
    }

    public function getKey()
    {
        return 123;
    }
}

it('builds resetUrl using provided email when available', function () {
    Config::set('app.frontend_url', 'http://test-frontend.local');

    $token = 'url-token-xyz';
    $email = 'provided@example.test';

    $notification = new QueuedResetPassword($token, $email);

    $ref = new ReflectionClass($notification);
    $method = $ref->getMethod('resetUrl');
    $method->setAccessible(true);

    $url = $method->invoke($notification, new DummyNotifiable);

    expect($url)->toContain('http://test-frontend.local/reset-password');
    expect($url)->toContain('token='.urlencode($token));
    expect($url)->toContain('email='.urlencode($email));
});

it('builds resetUrl using notifiable email when none provided', function () {
    Config::set('app.frontend_url', 'http://test-frontend.local');

    $token = 'url-token-xyz';

    $notification = new QueuedResetPassword($token, null);

    $ref = new ReflectionClass($notification);
    $method = $ref->getMethod('resetUrl');
    $method->setAccessible(true);

    $url = $method->invoke($notification, new DummyNotifiable);

    expect($url)->toContain('http://test-frontend.local/reset-password');
    expect($url)->toContain('token='.urlencode($token));
    expect($url)->toContain('email='.urlencode('dummy@notifiable.test'));
});
