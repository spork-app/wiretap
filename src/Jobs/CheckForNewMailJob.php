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
        $labels = $service->findAllLabels();

        foreach ($emails as $email) {
            if (in_array($email['id'], $processedEmails)) {
                continue;
            }

            $toAddresses = array_filter($email['to'], fn ($to) => $to->mailbox !== $user);

            foreach ($toAddresses as $address) {
                $existingLabels = array_values(array_filter($labels, fn ($label) => $label['name'] === $address->mailbox));

                // Create the label if it doesn't exist, and then refresh the variables.
                if (empty($existingLabels)) {
                    $service->createLabel($address->mailbox);

                    $labels = $service->findAllLabels();
                
                    $existingLabels = array_values(array_filter($labels, fn ($label) => $label['name'] === $address->mailbox));
                }
                // We should now be guarenteed that the label exists.
                foreach ($existingLabels as $label) {
                    $service->applyLabelToMessages($label['name'], $email['id']);
                }
            }
            $processedEmails[] = $email['id'];
            file_put_contents(storage_path('processed_emails.json'), json_encode($processedEmails));
        }
    }
}