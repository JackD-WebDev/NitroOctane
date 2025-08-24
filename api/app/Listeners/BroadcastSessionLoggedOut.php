<?php

namespace App\Listeners;

use App\Events\SessionLoggedOut;
use Illuminate\Auth\Events\OtherDeviceLogout;

class BroadcastSessionLoggedOut
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(OtherDeviceLogout $event)
    {
        broadcast(new SessionLoggedOut($event->user))->toOthers();
    }
}
