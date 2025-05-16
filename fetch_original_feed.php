<?php
// Simple script to fetch and display the raw content of the RSS feed
$feedUrl = 'http://lst.lat/rss/9';

echo "Fetching feed from {$feedUrl}...\n\n";

// Try using cURL to fetch the feed
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);

    echo "HTTP Status: " . $info['http_code'] . "\n";

    if ($error) {
        echo "cURL Error: " . $error . "\n\n";
    }

    curl_close($ch);

    if ($response) {
        echo "Content (first 1000 chars):\n";
        echo substr($response, 0, 1000) . "\n";

        // Save the fetched content for further analysis
        file_put_contents(__DIR__ . '/public/feeds/scraped/original_feed_9.xml', $response);
        echo "\nFull content saved to: " . __DIR__ . '/public/feeds/scraped/original_feed_9.xml' . "\n";
    } else {
        echo "No content received\n";
    }
} else {
    // Fallback to file_get_contents
    $response = @file_get_contents($feedUrl);

    if ($response === false) {
        echo "Failed to fetch feed using file_get_contents.\n";
    } else {
        echo "Content (first 1000 chars):\n";
        echo substr($response, 0, 1000) . "\n";

        // Save the fetched content for further analysis
        file_put_contents(__DIR__ . '/public/feeds/scraped/original_feed_9.xml', $response);
        echo "\nFull content saved to: " . __DIR__ . '/public/feeds/scraped/original_feed_9.xml' . "\n";
    }
}

// Try to identify the specific error in the XML
echo "\nAttempting to identify XML errors:\n";
libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);

if ($xml === false) {
    echo "XML parsing failed. Errors:\n";
    foreach (libxml_get_errors() as $error) {
        echo "Line {$error->line}, Column {$error->column}: {$error->message}\n";
    }
    libxml_clear_errors();
}
