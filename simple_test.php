<?php

// Create a simple Feed mock
class Feed {
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

// Clean HTML function
function cleanText($text) {
    // Remove invalid XML characters
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

    // Convert to valid UTF-8
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

    // Balance HTML
    $text = balanceHtml($text);

    return $text;
}

// Balance HTML function
function balanceHtml($html) {
    // Remove script, style, and comment tags
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
    $html = preg_replace('/<!--(.*?)-->/s', '', $html);

    // Find and remove incomplete tags that could cause XML issues
    $html = preg_replace('/<[^>]*$/s', '', $html);

    // List of self-closing tags
    $selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link'];

    // Ensure proper XML format for self-closing tags
    foreach ($selfClosingTags as $tag) {
        $html = preg_replace('/<(' . $tag . ')([^>]*[^\/>])>/i', '<$1$2 />', $html);
    }

    // Attempt to close unclosed tags
    $openTags = [];
    preg_match_all('/<([a-z]+)[^>]*>/i', $html, $matches);

    foreach ($matches[1] as $tag) {
        if (!in_array(strtolower($tag), $selfClosingTags)) {
            $openTags[] = $tag;
        }
    }

    preg_match_all('/<\/([a-z]+)>/i', $html, $matches);
    foreach ($matches[1] as $tag) {
        $index = array_search($tag, array_reverse($openTags, true));
        if ($index !== false) {
            unset($openTags[$index]);
        }
    }

    while (!empty($openTags)) {
        $tag = array_pop($openTags);
        $html .= '</' . $tag . '>';
    }

    return $html;
}

// Generate XML
$feed = new Feed();

// XML declaration and root element
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\"";
$xml .= " xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"";
$xml .= " xmlns:dc=\"http://purl.org/dc/elements/1.1/\"";
$xml .= " xmlns:media=\"http://search.yahoo.com/mrss/\">\n";

// Channel element
$xml .= "  <channel>\n";

// Basic channel metadata
$xml .= "    <title>" . htmlspecialchars($feed->title) . "</title>\n";
$xml .= "    <link>" . htmlspecialchars($feed->site_url) . "</link>\n";
$xml .= "    <description>" . htmlspecialchars($feed->description) . "</description>\n";

// Add atom:link for feed self-reference
$feedUrl = "http://example.com/feed_{$feed->id}.xml";
$xml .= "    <atom:link href=\"" . htmlspecialchars($feedUrl) . "\" rel=\"self\" type=\"application/rss+xml\" />\n";

// Add additional channel information
$xml .= "    <language>vi</language>\n";
$xml .= "    <generator>RSS Feed Generator</generator>\n";
$xml .= "    <lastBuildDate>" . date(DATE_RFC2822) . "</lastBuildDate>\n";
$xml .= "    <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";
$xml .= "    <ttl>60</ttl>\n"; // Time to live in minutes

// Add items to the feed
foreach ($items as $item) {
    $xml .= "    <item>\n";

    // Title
    $title = cleanText($item['title'] ?: 'No Title');
    $xml .= "      <title>" . htmlspecialchars($title) . "</title>\n";

    // Link
    $link = $item['link'] ?: $feed->site_url;
    $xml .= "      <link>" . htmlspecialchars($link) . "</link>\n";

    // GUID (unique identifier)
    $xml .= "      <guid isPermaLink=\"true\">" . htmlspecialchars($link) . "</guid>\n";

    // Description - use proper CDATA structure
    $description = cleanText($item['description'] ?: 'No description available.');

    // Only use CDATA if the description contains HTML
    if (strip_tags($description) !== $description) {
        $xml .= "      <description><![CDATA[" . $description . "]]></description>\n";
    } else {
        $xml .= "      <description>" . htmlspecialchars($description) . "</description>\n";
    }

    // Full content with CDATA if different from description
    if (!empty($item['content']) && $item['content'] !== $item['description']) {
        $content = cleanText($item['content']);
        if (strip_tags($content) !== $content) {
            $xml .= "      <content:encoded><![CDATA[" . $content . "]]></content:encoded>\n";
        } else {
            $xml .= "      <content:encoded>" . htmlspecialchars($content) . "</content:encoded>\n";
        }
    }

    // Publication date
    $xml .= "      <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";

    // Image as media:content
    if (!empty($item['image'])) {
        $xml .= "      <media:content url=\"" . htmlspecialchars($item['image']) . "\" medium=\"image\" />\n";
        $xml .= "      <enclosure url=\"" . htmlspecialchars($item['image']) . "\" type=\"image/jpeg\" length=\"0\" />\n";
    }

    // Categories
    if (!empty($item['categories'])) {
        foreach ($item['categories'] as $category) {
            $xml .= "      <category>" . htmlspecialchars($category) . "</category>\n";
        }
    }

    // Author
    if (!empty($item['author'])) {
        $xml .= "      <dc:creator>" . htmlspecialchars($item['author']) . "</dc:creator>\n";
    }

    $xml .= "    </item>\n";
}

// Close channel and rss elements
$xml .= "  </channel>\n";
$xml .= "</rss>";

// Validate the XML
$isValid = false;
$errorMessage = "";

try {
    $dom = new DOMDocument();
    $isValid = $dom->loadXML($xml);

    if (!$isValid) {
        $errorMessage = "XML is not valid";
    } else {
        $errorMessage = "XML is valid!";
    }
} catch (Exception $e) {
    $errorMessage = "Error validating XML: " . $e->getMessage();
}

// Save to file
$outputPath = __DIR__ . '/public/feeds/scraped/test_feed.xml';
file_put_contents($outputPath, $xml);

echo "XML generation result: " . $errorMessage . "\n";
echo "XML file saved to: " . $outputPath;
