<?php

namespace App\Listeners;

use App\Events\EmailVerified;
use Illuminate\Auth\Events\Verified;

class BroadcastEmailVerified
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(Verified $event)
    {
        broadcast(new EmailVerified($event->user))->toOthers();
    }
}
