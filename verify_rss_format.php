<?php

// Create a simple Feed mock
class Feed {
    public $id = 7;
    public $title = 'Báo Mới - Tin tức 24H, đọc báo mới nhanh nhất hôm nay';
    public $description = 'Generated RSS feed';
    public $site_url = 'https://baomoi.com/';
}

// Create sample items similar to the reference feed
$items = [
    [
        'title' => 'Leny Yoro',
        'link' => 'https://baomoi.com/leny-yoro-t39969445.epi20dd544b834603b2f4dad129bd5d3958',
        'description' => 'Leny Yoro - Tin tức 24h: Mối lo lớn của Amorim khi MU đá chung kết Europa League; MU và Tottenham bị chế giễu trước chung kết Europa League; MU còn nhiều nỗi sợ hãi hơn chung kết Europa League...',
        'date' => 'Fri, 16 May 2025 01:07:00 GMT',
        'content' => 'Content detail goes here'
    ],
    [
        'title' => 'Lamine Yamal',
        'link' => 'https://baomoi.com/lamine-yamal-tag14510.epid9040c15fd60e32ba368753ba28182f9',
        'description' => 'Lamine Yamal - Tin tức 24h: Barcelona vô địch La Liga 2024-25 sớm trước 2 vòng đấu; Yamal lập siêu phẩm, Barcelona chính thức đăng quang La Liga; Lamine Yamal lập siêu phẩm, Barcelona vô địch sớm La Liga...',
        'date' => 'Fri, 16 May 2025 01:06:00 GMT'
    ]
];

// Helper functions
function cleanText($text) {
    // Remove invalid XML characters
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    // Convert to valid UTF-8
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    return $text;
}

function formatDate($dateString) {
    $timestamp = strtotime($dateString);
    if ($timestamp) {
        return date(DATE_RFC2822, $timestamp);
    }
    return date(DATE_RFC2822);
}

// Generate XML
$feed = new Feed();
$xml = generateRssFeed($feed, $items);

// Function to generate RSS feed in the same format as rss.app
function generateRssFeed($feed, $items) {
    // XML declaration and root element
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\"";
    $xml .= " xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"";
    $xml .= " xmlns:dc=\"http://purl.org/dc/elements/1.1/\"";
    $xml .= " xmlns:media=\"http://search.yahoo.com/mrss/\">\n";

    // Channel element
    $xml .= "  <channel>\n";

    // Basic channel metadata
    $xml .= "    <title><![CDATA[" . $feed->title . "]]></title>\n";
    $xml .= "    <link>" . htmlspecialchars($feed->site_url) . "</link>\n";
    $xml .= "    <description><![CDATA[" . $feed->description . "]]></description>\n";

    // Add atom:link
    $feedUrl = "https://rss.app/feeds/" . $feed->id . ".xml";
    $xml .= "    <atom:link href=\"" . htmlspecialchars($feedUrl) . "\" rel=\"self\" type=\"application/rss+xml\" />\n";

    // Add additional channel information
    $xml .= "    <language>vi</language>\n";
    $xml .= "    <generator>RSS Feed Generator</generator>\n";
    $xml .= "    <lastBuildDate>" . date(DATE_RFC2822) . "</lastBuildDate>\n";
    $xml .= "    <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";

    // Add items to the feed
    foreach ($items as $item) {
        $xml .= "    <item>\n";

        // Title with CDATA
        $title = cleanText($item['title'] ?? 'No Title');
        $xml .= "      <title><![CDATA[" . $title . "]]></title>\n";

        // Link
        $link = $item['link'] ?? $feed->site_url;
        $xml .= "      <link>" . htmlspecialchars($link) . "</link>\n";

        // GUID (unique identifier)
        $xml .= "      <guid isPermaLink=\"true\">" . htmlspecialchars($link) . "</guid>\n";

        // Description with CDATA
        $description = cleanText($item['description'] ?? 'No description available.');
        $xml .= "      <description><![CDATA[" . $description . "]]></description>\n";

        // Publication date
        if (!empty($item['date'])) {
            $xml .= "      <pubDate>" . $item['date'] . "</pubDate>\n";
        } else {
            $xml .= "      <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";
        }

        $xml .= "    </item>\n";
    }

    // Close channel and rss elements
    $xml .= "  </channel>\n";
    $xml .= "</rss>";

    return $xml;
}

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
$outputPath = __DIR__ . '/public/feeds/scraped/test_rss_app_format.xml';
file_put_contents($outputPath, $xml);

echo "XML generation result: " . $errorMessage . "\n";
echo "XML file saved to: " . $outputPath . "\n\n";
echo "Sample of generated XML:\n\n";
echo substr($xml, 0, 500) . "...\n";
