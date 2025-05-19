<?php

// Bootstrap Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Define the output file paths
$jsonOutputFile = __DIR__ . '/public/feeds/scraped/latest_feed.json';
$fixedJsonOutputFile = __DIR__ . '/public/feeds/scraped/latest_feed_fixed.json';

// Set initial status
$xmlStatus = -1;
$jsonStatus = -1;

try {
    // Run the command to fetch feeds in XML format
    $xmlStatus = $kernel->call('feeds:fetch');
} catch (\Exception $e) {
    echo "Error running XML feed fetcher: " . $e->getMessage() . "\n";
}

try {
    // Run the command to fetch feeds in JSON format
    $jsonStatus = $kernel->call('feeds:fetch-json', [
        '--output' => $jsonOutputFile
    ]);
} catch (\Exception $e) {
    echo "Error running JSON feed fetcher: " . $e->getMessage() . "\n";
}

// Output status
if ($jsonStatus === 0) {
    echo "JSON feeds updated successfully at " . date('Y-m-d H:i:s') . "\n";
    echo "JSON output saved to {$jsonOutputFile}\n";
    echo "Fixed format JSON output saved to {$fixedJsonOutputFile}\n";

    if ($xmlStatus === 0) {
        echo "XML feeds also updated successfully.\n";
    } else {
        echo "XML feed update skipped or failed with status code {$xmlStatus}\n";
    }
} else {
    echo "Error updating RSS feeds at " . date('Y-m-d H:i:s') . "\n";
    if ($xmlStatus !== 0) {
        echo "XML feed update failed with status code {$xmlStatus}\n";
    }
    if ($jsonStatus !== 0) {
        echo "JSON feed update failed with status code {$jsonStatus}\n";
    }
}

// Terminate the application
$kernel->terminate(
    \Illuminate\Http\Request::capture(),
    new \Illuminate\Http\Response()
);
