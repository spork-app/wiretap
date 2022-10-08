<?php

namespace Spork\Wiretap\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Spork\Wiretap\Events\Emails\MailRecievedEvent;
use Spork\Wiretap\Services\ImapService;

class CheckForNewMailJob implements ShouldQueue
{
    public function handle()
    {
        $service = app(ImapService::class);

        $emails = $service->findAllFromDate(now()->startOfDay());

        $processedEmails = json_decode(file_get_contents(storage_path('processed_emails.json')));

        foreach ($emails as $email) {
            if (in_array($email['id'], $processedEmails)) {
                continue;
            }

            event(new MailRecievedEvent($email));

            $processedEmails[] = $email['id'];
        }
        file_put_contents(storage_path('processed_emails.json'), json_encode($processedEmails));
    }
}