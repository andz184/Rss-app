<?php
// Testing script to verify that the feed format works properly

// Simple function to get HTML content
function getWebContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

// Function to sanitize XML content
function cleanText($text) {
    // Remove invalid XML characters
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    // Convert to valid UTF-8
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    return $text;
}

// Function to get a favicon from a domain
function getFavicon($url) {
    $parsedUrl = parse_url($url);
    if (!isset($parsedUrl['host'])) {
        return 'https://example.com/favicon.ico';
    }

    $domain = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' . $parsedUrl['host'] : 'https://' . $parsedUrl['host'];
    return $domain . '/favicon.ico';
}

// Function to generate test RSS for n8n compatibility
function generateTestRSS($title, $description, $items, $baseUrl) {
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<rss version=\"2.0\">\n";
    $xml .= "  <channel>\n";
    $xml .= "    <title>" . htmlspecialchars(cleanText($title)) . "</title>\n";
    $xml .= "    <link>" . htmlspecialchars($baseUrl) . "</link>\n";
    $xml .= "    <description>" . htmlspecialchars(cleanText($description)) . "</description>\n";
    $xml .= "    <language>vi</language>\n";
    $xml .= "    <lastBuildDate>" . date(DATE_RFC2822) . "</lastBuildDate>\n";

    // Add items
    foreach ($items as $item) {
        $xml .= "    <item>\n";
        $xml .= "      <title>" . htmlspecialchars(cleanText($item['title'])) . "</title>\n";
        $xml .= "      <link>" . htmlspecialchars($item['link']) . "</link>\n";
        // Strip HTML tags for description
        $description = strip_tags($item['description']);
        $xml .= "      <description>" . htmlspecialchars($description) . "</description>\n";
        $xml .= "      <pubDate>" . $item['pubDate'] . "</pubDate>\n";
        $xml .= "      <guid>" . htmlspecialchars($item['link']) . "</guid>\n";
        $xml .= "    </item>\n";
    }

    $xml .= "  </channel>\n";
    $xml .= "</rss>";

    return $xml;
}

// Create some sample content for the feed
$testSource = "https://example.com/tech-news";
$testTitle = "Tin tức công nghệ";
$testDescription = "Cập nhật tin tức công nghệ mới nhất";
$testBaseUrl = "http://localhost:8000/rss/test";

// Generate some test items - these could come from any source
$testItems = [
    [
        'title' => 'Bài viết công nghệ số 1',
        'description' => 'Đây là phần mô tả ngắn gọn về bài viết công nghệ đầu tiên',
        'link' => 'https://example.com/post1',
        'pubDate' => date(DATE_RFC2822)
    ],
    [
        'title' => 'Bài viết công nghệ số 2',
        'description' => 'Đây là phần mô tả ngắn gọn về bài viết công nghệ thứ hai',
        'link' => 'https://example.com/post2',
        'pubDate' => date(DATE_RFC2822, strtotime('-1 day'))
    ],
    [
        'title' => 'Bài viết công nghệ số 3',
        'description' => 'Đây là phần mô tả ngắn gọn về bài viết công nghệ thứ ba',
        'link' => 'https://example.com/post3',
        'pubDate' => date(DATE_RFC2822, strtotime('-2 days'))
    ]
];

// Generate the test RSS
$xml = generateTestRSS($testTitle, $testDescription, $testItems, $testBaseUrl);

// Save the test RSS
$outputFile = __DIR__ . '/public/feeds/scraped/test_feed.xml';
if (!is_dir(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0755, true);
}

file_put_contents($outputFile, $xml);
echo "Test feed saved to: {$outputFile}\n";

// Now update routes to serve this test feed
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);

    // Check if our route override is already in place
    if (strpos($routesContent, 'Custom route for test feed') === false) {
        // Add our custom route
        $newRoute = "
// Custom route for test feed
Route::get('/rss/test', function() {
    \$filePath = public_path('feeds/scraped/test_feed.xml');
    return response(file_get_contents(\$filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.test');
";

        // Add our route to the routes file
        $updatedContent = str_replace("Auth::routes();", $newRoute . "\nAuth::routes();", $routesContent);
        file_put_contents($routesFile, $updatedContent);
        echo "Added route for test feed at /rss/test\n";
    } else {
        echo "Route already exists.\n";
    }
} else {
    echo "Routes file not found.\n";
}

// Instructions
echo "\nYou can now access the test feed at: http://localhost:8000/rss/test\n";
echo "Please restart your Laravel server for the changes to take effect.\n";

// Create an HTML viewer to compare feeds
$comparisonHtml = '<!DOCTYPE html>
<html>
<head>
    <title>RSS Feed Comparison</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .comparison { display: flex; }
        .feed { flex: 1; margin: 10px; padding: 15px; border: 1px solid #ddd; }
        h2 { color: #444; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>RSS Feed Comparison</h1>
    <div class="comparison">
        <div class="feed">
            <h2>Reference Feed</h2>
            <p><a href="http://localhost:8000/rss/reference" target="_blank">View Feed</a></p>
            <pre id="reference-feed">Loading...</pre>
        </div>
        <div class="feed">
            <h2>Test Feed</h2>
            <p><a href="http://localhost:8000/rss/test" target="_blank">View Feed</a></p>
            <pre id="test-feed">Loading...</pre>
        </div>
    </div>
    <script>
        // Function to escape HTML for display
        function escapeHtml(html) {
            return html
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/\'/g, "&#039;");
        }

        // Function to fetch and display feeds
        async function fetchFeed(url, elementId) {
            try {
                const response = await fetch(url);
                const text = await response.text();
                document.getElementById(elementId).textContent = text;
            } catch (error) {
                document.getElementById(elementId).textContent = "Error loading feed: " + error.message;
            }
        }

        // Load feeds
        window.onload = function() {
            fetchFeed("http://localhost:8000/rss/reference", "reference-feed");
            fetchFeed("http://localhost:8000/rss/test", "test-feed");
        };
    </script>
</body>
</html>';

file_put_contents(__DIR__ . '/public/feed-comparison.html', $comparisonHtml);
echo "Feed comparison viewer created at: http://localhost:8000/feed-comparison.html\n";
