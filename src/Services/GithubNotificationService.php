<?php

namespace Spork\Wiretap\Services;

use App\Models\User;
use App\Notifications\GithubNotification;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Notification;

class GithubNotificationService 
{
    public function findNotifications()
    {
        // use guzzle to query the github api
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://api.github.com/notifications?all=false&per_page=1', [
            'headers' => [
                'Authorization' => 'token ' . getenv('GITHUB_NOTIFICATION_TOKEN')
            ]
        ]);

        $notifications = json_decode($response->getBody()->getContents());

        foreach ($notifications as $notification) {
            $githubNotification = new GithubNotification($notification);

            User::first()->notify($githubNotification);
        }

        $this->markAllAsRead();
    }

    public function markAllAsRead(): bool
    {
        // use guzzle to query the github api
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->put('https://api.github.com/notifications', [
                'headers' => [
                    'Authorization' => 'token ' . getenv('GITHUB_NOTIFICATION_TOKEN')
                ],
                'json' => [
                    'last_read_at' => now(),
                    'read' => true,
                ]
            ]);

            return in_array($response->getStatusCode(), [202, 205, 304]);
        } catch (ClientException $e) {
            return false;
        }
    }
}