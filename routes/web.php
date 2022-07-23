<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spork\Wiretap\Events\News\ArticleHiddenEvent;
use Spork\Wiretap\Events\News\ArticleReadEvent;
use Spork\Wiretap\Events\Research\LinkAddedToResearch;

Route::middleware('auth:sanctum')->post('/wiretap/track', function (Request $request) {
    $event = match ($request->get('event', null)) {
        'article.read' => new ArticleReadEvent($request->user(), $request->get('context')),
        'article.hidden' => new ArticleHiddenEvent($request->user(), $request->get('context')),
        'research.added' => new LinkAddedToResearch($request->user(), $request->get('context')),
        // 'recipe.cooked' => new RecipeCookedEvent($request->user(), $request->get('context')),
        default => null,
    };
    
    if (empty($event)) {
        return response()->json([
            
        ], 200);
    }

    return response()->json([
        'event' => $event,
    ], 200);
});