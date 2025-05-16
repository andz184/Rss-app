<?php
// Automatically regenerate an RSS feed with the new format

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Process arguments
$feedId = $argv[1] ?? null;

if (empty($feedId)) {
    echo "Usage: php regenerate_feed.php [feed_id]\n";
    exit(1);
}

try {
    // Get the feed service and controller
    $controller = $app->make(App\Http\Controllers\WebScraperController::class);

    // Find the feed
    $feed = App\Models\Feed::findOrFail($feedId);

    // Regenerate the feed file
    $method = new ReflectionMethod(App\Http\Controllers\WebScraperController::class, 'generateScrapedFeedFile');
    $method->setAccessible(true);
    $result = $method->invoke($controller, $feed);

    if ($result) {
        $filePath = public_path('feeds/scraped/feed_' . $feed->id . '.xml');
        echo "Feed regenerated successfully.\n";
        echo "Feed file saved to: " . $filePath . "\n";

        // Show first 200 characters of the feed
        $content = file_get_contents($filePath);
        echo "\nFirst 200 characters of the feed:\n";
        echo substr($content, 0, 200) . "...\n";
    } else {
        echo "Failed to regenerate feed file.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
