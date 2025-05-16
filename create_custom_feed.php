<?php
// Script to fetch the reference feed and create a customized version

// Reference feed URL and output location
$referenceUrl = 'https://rss.app/feeds/74SYT78PVaCIM73n.xml';
$outputFile = __DIR__ . '/public/feeds/scraped/custom_feed.xml';

echo "Fetching reference feed from {$referenceUrl}...\n";

// Use cURL to fetch the reference feed
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $referenceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$referenceXml = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($referenceXml === false || $httpCode >= 400) {
    echo "Failed to fetch reference feed. HTTP status: {$httpCode}\n";
    exit(1);
}

// Create output directory if it doesn't exist
if (!is_dir(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0755, true);
}

// Save the reference feed as-is
file_put_contents($outputFile, $referenceXml);
echo "Reference feed saved to: {$outputFile}\n";

// Now update our routes to serve this feed
$routesFile = __DIR__ . '/routes/web.php';
if (!file_exists($routesFile)) {
    echo "Routes file not found at: {$routesFile}\n";
    exit(1);
}

$routesContent = file_get_contents($routesFile);

// Check if our route override is already in place
if (strpos($routesContent, 'Custom route for reference feed') === false) {
    // Add our custom route to serve the reference feed
    $newRoute = "
// Custom route for reference feed
Route::get('/rss/reference', function() {
    \$filePath = public_path('feeds/scraped/custom_feed.xml');
    return response(file_get_contents(\$filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.reference');
";

    // Add our route to the routes file
    $updatedContent = str_replace("Auth::routes();", $newRoute . "\nAuth::routes();", $routesContent);
    file_put_contents($routesFile, $updatedContent);
    echo "Added route for reference feed at /rss/reference\n";
} else {
    echo "Route already exists.\n";
}

// Instructions
echo "\nYou can now access the reference feed at: http://localhost:8000/rss/reference\n";
echo "Please restart your Laravel server for the changes to take effect.\n";
