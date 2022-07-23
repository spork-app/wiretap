<?php

namespace Spork\Wiretap\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spork\Wiretap\Services\ImapService;

class CheckForNewMailJob implements ShouldQueue
{
    public function handle()
    {
        $service = app(ImapService::class);

        $emails = $service->findAllUnread();

        $processedEmails = json_decode(file_get_contents(storage_path('processed_emails.json')));
        [$user, $domain] = explode('@', env('IMAP_USERNAME'));

        foreach ($emails as $email) {
            if (in_array($email['id'], $processedEmails)) {
                continue;
            }

            $toAddresses = array_filter($email['to'], fn ($to) => $to->mailbox !== $user);

            foreach ($toAddresses as $address) {
                $existingLabel = $service->findLabel($address->mailbox);
                // Create the label if it doesn't exist, and then refresh the variables.
                if (empty($existingLabel)) {
                    $service->createLabel($address->mailbox);
                    cache()->forget('imap.label.'.strtolower($address->mailbox));
                    $existingLabel = $service->findLabel($address->mailbox);
                }

                if (empty($existingLabel)) {
                    dd($existingLabel, $address->mailbox);
                }

                // We should now be guarenteed that the label exists.
                $service->applyLabelToMessages($existingLabel['name'], $email['id']);
            }
            $processedEmails[] = $email['id'];
        }
        file_put_contents(storage_path('processed_emails.json'), json_encode($processedEmails));
    }
}