<?php

namespace Spork\Wiretap\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spork\Wiretap\Events\Emails\MailRecievedEvent;

class IdentifyPackagesInMailListener implements ShouldQueue
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

    public function handle(MailRecievedEvent $event)
    {
        // $event->to, $event->id, $event->
        $email = $event->email;
    }
}
