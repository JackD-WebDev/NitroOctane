<?php

namespace App\Models;

use App\Traits\HasOptimizedUuids;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Notifications\Notifiable;
use App\Notifications\QueuedResetPassword;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasOptimizedUuids, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'lang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lang' => 'string',
        ];
    }

    /**
     * Send the password reset notification as a queued mail.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $email = $this->getEmailForPasswordReset();
        $this->notify(new QueuedResetPassword($token, $email));
    }

    /**
     * Send the email verification notification as a queued mail.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * Ensure queued notifications can resolve the mail recipient when
     * processed by a worker. Sometimes queued model resolution can omit
     * transient route data, so explicitly return the user's email.
     *
     * @param  string|null  $driver
     */
    public function routeNotificationForMail($driver = null): ?string
    {
        // The framework may call routeNotificationFor('mail', $notification)
        // and pass the Notification instance as the second argument. Older
        // signatures typed $driver as ?string which caused a TypeError when
        // a Notification object was provided. Normalize object inputs here
        // to be defensive and return the user's email.
        if (is_object($driver)) {
            $driver = null;
        }

        return $this->email ?? $this->getEmailForPasswordReset();
    }
}
