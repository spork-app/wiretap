<?php

namespace Spork\Wiretap\Services;

use Carbon\Carbon;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Support\FolderCollection;

class ImapService 
{
    public function findAllUnread()
    {
        $mailbox = imap_open("{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}INBOX", env("IMAP_USERNAME"), env("IMAP_PASSWORD"), OP_READONLY);
        $emails = imap_search($mailbox, 'UNSEEN');
        $supportedDomains = explode(',', env('IMAP_DOMAIN'));
        return array_map(function ($email) use ($mailbox, $supportedDomains) {
            $headers = imap_headerinfo($mailbox, $email);
            try {
            return [
                'id' => $email,
                'date' => Carbon::parse($headers->MailDate),
                // We should only return to addresses that are in the list of supported domains.
                'to' => array_values(array_filter($headers->to ?? [], fn ($to) => in_array($to->host, $supportedDomains))),
                'from' => $headers->from,
                'unseen' => $headers->Unseen === 'U',
            ];
        } catch (\Throwable $e) {
            dd($e, $headers);
        }
        }, $emails);
    }

    public function findAllFromDate(Carbon $date)
    {
        $mailbox = imap_open("{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}INBOX", env("IMAP_USERNAME"), env("IMAP_PASSWORD"), OP_READONLY);
        $emails = imap_search($mailbox, 'SINCE "' . $date->format('d-M-Y') . '"');
        $supportedDomains = explode(',', env('IMAP_DOMAIN'));
        return array_map(function ($email) use ($mailbox, $supportedDomains) {
            $headers = imap_headerinfo($mailbox, $email);
            try {
            return [
                'id' => $email,
                'date' => Carbon::parse($headers->MailDate),
                // We should only return to addresses that are in the list of supported domains.
                'to' => array_values(array_filter($headers->to ?? [], fn ($to) => in_array($to->host, $supportedDomains))),
                'from' => $headers->from,
                'unseen' => $headers->Unseen === 'U',
            ];
        } catch (\Throwable $e) {
            dd($e, $headers);
        }
        }, $emails);
    }

    public function createLabel($label)
    {
        $mailbox = imap_open("{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}INBOX", env("IMAP_USERNAME"), env("IMAP_PASSWORD"), OP_READONLY);
        imap_createmailbox($mailbox, "{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}".imap_utf7_encode($label));
        imap_close($mailbox);
    }

    public function findAllLabels()
    {
        $mailbox = imap_open("{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}INBOX", env("IMAP_USERNAME"), env("IMAP_PASSWORD"), OP_READONLY);
        $labels = imap_getmailboxes($mailbox, "{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}", '%');

        return array_values(array_filter(array_map(function($label) {
            return [
                'id' => $label->name,
                'name' => explode('}', $label->name)[1],
                'attributes' => $label->attributes,
                'delimiter' => $label->delimiter,
            ];
        }, $labels), fn ($label) => !in_array($label['name'], ['INBOX', '[Gmail]'])));
    }

    public function findLabel(string $name)
    {
        return cache()->remember('imap.label.'.$name, now()->addMinutes(30), function () use ($name) {
            $mailbox = imap_open("{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}INBOX", env("IMAP_USERNAME"), env("IMAP_PASSWORD"), OP_READONLY);
            $labels = imap_getmailboxes($mailbox, "{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}", imap_utf7_encode($name));

            return array_values(array_filter(array_map(function($label) {
                return [
                    'id' => $label->name,
                    'name' => explode('}', $label->name)[1],
                    'attributes' => $label->attributes,
                    'delimiter' => $label->delimiter,
                ];
            }, $labels), fn ($label) => !in_array($label['name'], ['INBOX', '[Gmail]'])));
        });
    }
    
    public function applyLabelToMessages($label, $messages)
    {
        $messages = implode(',', is_array($messages) ? $messages : [$messages]);

        $mailbox = imap_open("{" . env('IMAP_HOST') . ":".env("IMAP_PORT") ."/imap/ssl}INBOX", env("IMAP_USERNAME"), env("IMAP_PASSWORD"));
        if (!imap_mail_copy($mailbox, $messages, imap_utf7_encode($label))) {
            throw new \Exception(imap_last_error());
        }
        imap_close($mailbox);
    }
}   