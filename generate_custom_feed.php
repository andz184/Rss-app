<?php

// Bootstrap Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Define the custom feed data structure
$feedData = [
  "title" => "RSS Feed - www.artificialintelligence-news.com",
  "link" => "https://www.artificialintelligence-news.com/",
  "description" => "Generated RSS feed from https://www.artificialintelligence-news.com/",
  "language" => "vi",
  "lastBuildDate" => date('D, d M Y H:i:s O'),
  "items" => [
    [
      "title" => "The role of machine learning in enhancing cloud-native container security",
      "link" => "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Innovative machine learning uses transforming business applications",
      "link" => "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "AI and bots allegedly used to fraudulently boost music streams",
      "link" => "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "Best data security platforms of 2025",
      "link" => "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
      "pubDate" => date('D, d M Y H:i:s O')
    ],
    [
      "title" => "AI tool speeds up government feedback, experts urge caution",
      "link" => "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
      "description" => "No description available.",
      "guid" => "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
      "pubDate" => date('D, d M Y H:i:s O')
    ]
  ]
];

// Define the output file path
$outputFile = __DIR__ . '/public/feeds/scraped/custom_ai_feed.json';

// Ensure the directory exists
if (!file_exists(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0755, true);
}

// Save the feed data to a JSON file
file_put_contents($outputFile, json_encode($feedData, JSON_PRETTY_PRINT));

echo "Custom AI news feed generated successfully at " . date('Y-m-d H:i:s') . "\n";
echo "Output saved to {$outputFile}\n";

// Create a route to serve this custom feed
$routeFile = __DIR__ . '/routes/custom_feed.php';
$routeContent = <<<EOT
<?php

use Illuminate\\Support\\Facades\\Route;

// Custom AI News Feed route
Route::get('/json/ai-news', function() {
    \$filePath = public_path('feeds/scraped/custom_ai_feed.json');
    if (file_exists(\$filePath)) {
        return response()->json(json_decode(file_get_contents(\$filePath), true));
    } else {
        return response()->json(['error' => 'Feed file not found'], 404);
    }
})->name('json.ai-news');
EOT;

// Save the route file
file_put_contents($routeFile, $routeContent);

echo "Custom route file created at {$routeFile}\n";
echo "Access the feed at: /json/ai-news\n";
