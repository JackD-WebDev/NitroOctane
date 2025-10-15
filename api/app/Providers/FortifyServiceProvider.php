<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Http\Responses\LoginResponse;
use App\Actions\Fortify\CreateNewUser;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use App\Http\Responses\RegisterResponse;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Responses\VerifyEmailResponse;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Responses\PasswordUpdateResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use App\Http\Responses\TwoFactorEnabledResponse;
use App\Http\Responses\PasswordConfirmedResponse;
use App\Http\Responses\TwoFactorDisabledResponse;
use App\Http\Responses\TwoFactorConfirmedResponse;
use App\Http\Responses\FailedTwoFactorLoginResponse;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\RecoveryCodesGeneratedResponse;
use App\Http\Responses\ProfileInformationUpdatedResponse;
use App\Http\Responses\FailedPasswordConfirmationResponse;
use App\Http\Responses\EmailVerificationNotificationSentResponse;
use App\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorEnabledResponse as TwoFactorEnabledResponseContract;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Laravel\Fortify\Contracts\TwoFactorDisabledResponse as TwoFactorDisabledResponseContract;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\RecoveryCodesGeneratedResponse as RecoveryCodesGeneratedResponseContract;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;
use Laravel\Fortify\Contracts\FailedPasswordConfirmationResponse as FailedPasswordConfirmationResponseContract;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        /*
        |--------------------------------------------------------------------------
        | Fortify Response Bindings
        |--------------------------------------------------------------------------
        |
        | Register custom overrides for Fortify HTTP responses. These service
        | providers bind custom implementations for various Fortify response
        | contracts to the service container:
        | - LoginResponse
        | - LogoutResponse
        | - RegisterResponse
        | - VerifyEmailResponse
        | - PasswordUpdateResponse
        | - TwoFactorLoginResponse
        | - TwoFactorEnabledResponse
        | - TwoFactorDisabledResponse
        | - TwoFactorConfirmedResponse
        | - PasswordConfirmedResponse
        | - FailedTwoFactorLoginResponse
        | - RecoveryCodesGeneratedResponse
        | - ProfileInformationUpdatedResponse
        | - FailedPasswordConfirmationResponse
        | - EmailVerificationNotificationSentResponse
        | - SuccessfulPasswordResetLinkRequestResponse
        |
        */
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->bind(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->bind(VerifyEmailResponseContract::class, VerifyEmailResponse::class);
        $this->app->bind(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->bind(PasswordUpdateResponseContract::class, PasswordUpdateResponse::class);
        $this->app->bind(TwoFactorEnabledResponseContract::class, TwoFactorEnabledResponse::class);
        $this->app->bind(TwoFactorDisabledResponseContract::class, TwoFactorDisabledResponse::class);
        $this->app->bind(TwoFactorConfirmedResponseContract::class, TwoFactorConfirmedResponse::class);
        $this->app->bind(PasswordConfirmedResponseContract::class, PasswordConfirmedResponse::class);
        $this->app->bind(FailedTwoFactorLoginResponseContract::class, FailedTwoFactorLoginResponse::class);
        $this->app->bind(RecoveryCodesGeneratedResponseContract::class, RecoveryCodesGeneratedResponse::class);
        $this->app->bind(ProfileInformationUpdatedResponseContract::class, ProfileInformationUpdatedResponse::class);
        $this->app->bind(FailedPasswordConfirmationResponseContract::class, FailedPasswordConfirmationResponse::class);
        $this->app->bind(EmailVerificationNotificationSentResponseContract::class, EmailVerificationNotificationSentResponse::class);
        $this->app->bind(SuccessfulPasswordResetLinkRequestResponseContract::class, SuccessfulPasswordResetLinkRequestResponse::class);
    }
}
