<?php

namespace Spork\Wiretap\Listeners;

use Spork\Wiretap\Events\AbstractTappedEvent;
use MeiliSearch\Client;

class UpdateKnowledgeIndexListner
{
    /**
     * Handle the event.
     *
     * @param  \Spork\Wiretap\Events\News\ArticleReadEvent  $event
     * @return void
     */
    public function handle(AbstractTappedEvent $event)
    {
        $client = new Client(
            env('MEILISEARCH_HOST'),
            env('MEILISEARCH_KEY')
        );

        $index = $client->index('knowledge');

        $index->addDocuments([
            $event->linkData
        ]);
    }
}