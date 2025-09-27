<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;

uses(TestCase::class);
use App\Providers\FortifyServiceProvider;
use Illuminate\Support\Application;

it('binds all fortify response contracts to their implementations', function () {
    $app = $this->app;

    $app->register(FortifyServiceProvider::class);
    $app->boot();

    $bindings = [
        \Laravel\Fortify\Contracts\LoginResponse::class => \App\Http\Responses\LoginResponse::class,
        \Laravel\Fortify\Contracts\LogoutResponse::class => \App\Http\Responses\LogoutResponse::class,
        \Laravel\Fortify\Contracts\RegisterResponse::class => \App\Http\Responses\RegisterResponse::class,
        \Laravel\Fortify\Contracts\VerifyEmailResponse::class => \App\Http\Responses\VerifyEmailResponse::class,
        \Laravel\Fortify\Contracts\TwoFactorLoginResponse::class => \App\Http\Responses\TwoFactorLoginResponse::class,
        \Laravel\Fortify\Contracts\PasswordUpdateResponse::class => \App\Http\Responses\PasswordUpdateResponse::class,
        \Laravel\Fortify\Contracts\TwoFactorEnabledResponse::class => \App\Http\Responses\TwoFactorEnabledResponse::class,
        \Laravel\Fortify\Contracts\TwoFactorDisabledResponse::class => \App\Http\Responses\TwoFactorDisabledResponse::class,
        \Laravel\Fortify\Contracts\TwoFactorConfirmedResponse::class => \App\Http\Responses\TwoFactorConfirmedResponse::class,
        \Laravel\Fortify\Contracts\PasswordConfirmedResponse::class => \App\Http\Responses\PasswordConfirmedResponse::class,
        \Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse::class => \App\Http\Responses\FailedTwoFactorLoginResponse::class,
        \Laravel\Fortify\Contracts\RecoveryCodesGeneratedResponse::class => \App\Http\Responses\RecoveryCodesGeneratedResponse::class,
        \Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse::class => \App\Http\Responses\ProfileInformationUpdatedResponse::class,
        \Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse::class => \App\Http\Responses\FailedPasswordConfirmationResponse::class,
        \Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse::class => \App\Http\Responses\EmailVerificationNotificationSentResponse::class,
        \Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse::class => \App\Http\Responses\SuccessfulPasswordResetLinkRequestResponse::class,
    ];

    foreach ($bindings as $contract => $implementation) {
        expect($app->bound($contract))->toBeTrue();
    }
});
