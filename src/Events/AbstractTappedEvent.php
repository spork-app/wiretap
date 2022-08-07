<?php

namespace Spork\Wiretap\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

abstract class AbstractTappedEvent
{
    use SerializesModels;
    
    public function __construct(public User $user, public array $linkData)
    {    
    }
}