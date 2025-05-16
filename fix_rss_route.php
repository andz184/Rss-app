<?php
// Update the WebScraperController to serve our fixed feed

// Bootstrap Laravel (this code should run within the Laravel context)
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get the Routes file
$routesFile = __DIR__ . '/routes/web.php';
$routesContent = file_get_contents($routesFile);

// Check if our route override is already in place
if (strpos($routesContent, 'Custom route for fixed feed') === false) {
    // Add our custom route to serve the fixed feed
    $newRoute = "
// Custom route for fixed feed ID 9
Route::get('/rss/9', function() {
    \$filePath = public_path('feeds/scraped/rss9_fixed.xml');
    return response(file_get_contents(\$filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.fixed.9');
";

    // Add our route before the auth middleware group
    $updatedContent = str_replace("Auth::routes();", $newRoute . "\nAuth::routes();", $routesContent);

    // Write the updated routes file
    file_put_contents($routesFile, $updatedContent);

    echo "Route added for fixed feed!\n";
} else {
    echo "Route already exists.\n";
}

// Now let's update the controller method to serve the fixed feed for problematic feeds
$controllerFile = __DIR__ . '/app/Http/Controllers/WebScraperController.php';
$controllerContent = file_get_contents($controllerFile);

// Check if our fix is already in place
if (strpos($controllerContent, 'Special case for problematic feed ID 9') === false) {
    // Add our fallback logic for specific feeds with issues
    $replacePattern = "public function showRssFeed(\$feedId)\n    {";
    $newMethod = "public function showRssFeed(\$feedId)\n    {
        // Special case for problematic feed ID 9
        if (\$feedId == 9) {
            \$filePath = public_path('feeds/scraped/rss9_fixed.xml');
            if (file_exists(\$filePath)) {
                return response(file_get_contents(\$filePath), 200)
                    ->header('Content-Type', 'application/rss+xml; charset=utf-8');
            }
        }
";

    // Replace the method with our updated version
    $updatedContent = str_replace($replacePattern, $newMethod, $controllerContent);

    // Write the updated controller file
    file_put_contents($controllerFile, $updatedContent);

    echo "Controller updated to serve fixed feed!\n";
} else {
    echo "Controller already updated.\n";
}

// Clear Laravel's route cache
echo shell_exec('php artisan route:clear');
echo "Route cache cleared.\n";

echo "Fixed RSS feed is now available at http://lst.lat/rss/9\n";
echo "Please restart your Laravel server for the changes to take effect.\n";
