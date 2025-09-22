<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Events\EmailVerified;

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
