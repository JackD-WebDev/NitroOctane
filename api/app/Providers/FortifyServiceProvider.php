<?php

namespace App\Providers;

use App\Models\User;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\{
    Str,
    ServiceProvider
};
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\{
    CreateNewUser,
    ResetUserPassword,
    UpdateUserPassword,
    UpdateUserProfileInformation
};
use App\Http\Responses\{
    LoginResponse,
    LogoutResponse,
    RegisterResponse,
    VerifyEmailResponse,
    TwoFactorLoginResponse,
    PasswordUpdateResponse,
    PasswordConfirmedResponse,
    TwoFactorEnabledResponse,
    TwoFactorDisabledResponse,
    TwoFactorConfirmedResponse,
    FailedTwoFactorLoginResponse,
    RecoveryCodesGeneratedResponse,
    ProfileInformationUpdatedResponse,
    FailedPasswordConfirmationResponse,
    EmailVerificationNotificationSentResponse,
    SuccessfulPasswordResetLinkRequestResponse
};
use Laravel\Fortify\Contracts\{
    LoginResponse as LoginResponseContract,
    LogoutResponse as LogoutResponseContract,
    VerifyEmailResponse as VerifyEmailResponseContract,
    RegisterResponse as RegisterResponseContract,
    PasswordUpdateResponse as PasswordUpdateResponseContract,
    TwoFactorLoginResponse as TwoFactorLoginResponseContract,
    TwoFactorEnabledResponse as TwoFactorEnabledResponseContract,
    PasswordConfirmedResponse as PasswordConfirmedResponseContract,
    TwoFactorDisabledResponse as TwoFactorDisabledResponseContract,
    TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract,
    FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract,
    RecoveryCodesGeneratedResponse as RecoveryCodesGeneratedResponseContract,
    ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract,
    FailedPasswordConfirmationResponse as FailedPasswordConfirmationResponseContract,
    EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract,
    SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract
};

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
