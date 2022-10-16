<?php

namespace Spork\Wiretap\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Spork\Wiretap\Events\Emails\MailRecievedEvent;
use Spork\Wiretap\Services\ImapService;

class LabelNewMessageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        public ImapService $service
    ) {
        //
    }

    public function handle(MailRecievedEvent $event)
    {
        /** @var array $email */
        $email = $event->email;
        [$user, $domain] = explode('@', env('IMAP_USERNAME'));
        $toAddresses = array_filter($email['to'], fn ($to) => $to->mailbox !== $user);

        foreach ($toAddresses as $address) {
            $existingLabel = Arr::first($this->service->findLabel($address->mailbox));
            // Create the label if it doesn't exist, and then refresh the variables.
            if (empty($existingLabel)) {
                $this->service->createLabel($address->mailbox);
                cache()->forget('imap.label.'.strtolower($address->mailbox));
                $existingLabel = Arr::first($this->service->findLabel($address->mailbox));
            }

            if (empty($existingLabel)) {
                info("Found $existingLabel", [$address->mailbox]);
            }

            // We should now be guarenteed that the label exists.
            $this->service->applyLabelToMessages($existingLabel['name'], $email['id']);
        }
    }
}
