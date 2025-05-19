<?php

use Illuminate\Support\Facades\Route;

// Custom AI News Feed route
Route::get('/json/ai-news', function() {
    $filePath = public_path('feeds/scraped/custom_ai_feed.json');
    if (file_exists($filePath)) {
        return response()->json(json_decode(file_get_contents($filePath), true));
    } else {
        return response()->json(['error' => 'Feed file not found'], 404);
    }
})->name('json.ai-news');
