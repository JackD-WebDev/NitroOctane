<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\BroadcastSessionLoggedOut;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OtherDeviceLogout::class => [
            BroadcastSessionLoggedOut::class,
        ],
        \Illuminate\Auth\Events\Verified::class => [
            \App\Listeners\BroadcastEmailVerified::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
