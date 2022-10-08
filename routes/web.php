<?php

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use MeiliSearch\Client;
use MeiliSearch\Search\SearchResult;
use Spork\Wiretap\Events\Food\RecipeCookedEvent;
use Spork\Wiretap\Events\News\ArticleHiddenEvent;
use Spork\Wiretap\Events\News\ArticleReadEvent;
use Spork\Wiretap\Events\Research\LinkAddedToResearch;

Route::middleware('auth:sanctum')->post('/wiretap/track', function (Request $request) {
    $event = match ($request->get('event', null)) {
        'article.read' => new ArticleReadEvent($request->user(), $request->get('context')),
        'article.hidden' => new ArticleHiddenEvent($request->user(), $request->get('context')),
        'research.added' => new LinkAddedToResearch($request->user(), $request->get('context')),
        'recipe.cooked' => new RecipeCookedEvent($request->user(), $request->get('context')),
        default => null,
    };
    
    if (empty($event)) {
        return response()->json([
            
        ], 200);
    }
    
    // [ 'event' => 'article.read', 'context' => ]

    event($event);
    return response()->json([
        'event' => $event,
    ], 200);
});

Route::middleware('auth:sanctum')->get('/wiretap/knowledge', function (Request $request) {
    $client = new Client(
        env('MEILISEARCH_HOST'),
        env('MEILISEARCH_KEY')
    );

    $index = $client->index('knowledge');

    /** @var SearchResult $resutls */
    $results = $index->search($request->get('query'));

    return new LengthAwarePaginator(
        $results->getHits(),
        $results->getHitsCount(),
        $request->get('perPage', 10),
        $request->get('page', 1)
    );
});