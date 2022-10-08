<?php

namespace Spork\Wiretap\Events\Emails;

use Spork\Wiretap\Events\AbstractTappedEvent;

class MailRecievedEvent extends AbstractTappedEvent
{
    public function __construct(
        public array $email
    ) {
        
    }
}