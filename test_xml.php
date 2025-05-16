<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\App;
use App\Http\Controllers\WebScraperController;

// Create a simple Feed mock
class MockFeed {
    public $id = 7;
    public $title = 'Test Feed';
    public $description = 'Test feed description';
    public $site_url = 'http://example.com';
}

// Create a simple item
$items = [
    [
        'title' => 'Test Item',
        'link' => 'http://example.com/item',
        'description' => 'This is a <strong>test</strong> description',
        'content' => 'This is test content with <p>HTML tags that need to be properly handled</p>',
        'date' => date('Y-m-d H:i:s'),
        'image' => 'http://example.com/image.jpg',
        'categories' => ['Test', 'Example'],
        'author' => 'Test Author'
    ]
];

// Get an instance of the WebScraperController
$controller = App::make(WebScraperController::class);

// Generate XML
$feed = new MockFeed();
$method = new ReflectionMethod(WebScraperController::class, 'generateBeautifulXML');
$method->setAccessible(true);
$xml = $method->invoke($controller, $feed, $items);

// Save to file
file_put_contents(__DIR__ . '/public/feeds/scraped/test_feed.xml', $xml);

echo "XML generated at " . __DIR__ . '/public/feeds/scraped/test_feed.xml';
