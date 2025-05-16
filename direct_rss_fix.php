<?php

// This script directly fetches and fixes an RSS feed's format
// to match the structure from rss.app without database access

// Settings
$feedUrl = 'http://lst.lat/rss/9'; // The URL of the problematic feed
$outputFile = __DIR__ . '/public/feeds/scraped/fixed_feed_9.xml';

// Helper functions
function cleanText($text) {
    // Remove invalid XML characters
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    // Convert to valid UTF-8
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    // Balance HTML
    $text = balanceHtml($text);
    return $text;
}

function balanceHtml($html) {
    // Remove script, style, and comment tags
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
    $html = preg_replace('/<!--(.*?)-->/s', '', $html);

    // Find and remove incomplete tags that could cause XML issues
    $html = preg_replace('/<[^>]*$/s', '', $html);

    // List of self-closing tags
    $selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link', 'source', 'track', 'wbr', 'area', 'base', 'col', 'embed', 'param'];

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

try {
    // Step 1: Fetch the original feed
    echo "Fetching feed from {$feedUrl}...\n";
    $originalXml = @file_get_contents($feedUrl);

    if ($originalXml === false) {
        throw new Exception("Failed to fetch the feed from {$feedUrl}");
    }

    // Step 2: Parse the original XML to extract its content
    $feedData = new stdClass();
    $feedData->title = 'Feed Title';
    $feedData->description = 'Feed Description';
    $feedData->link = 'https://example.com';
    $feedItems = [];

    // Try to parse the XML, but handle errors gracefully
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($originalXml);

    if ($xml !== false) {
        // Extract feed information
        if (isset($xml->channel)) {
            $channel = $xml->channel;
            $feedData->title = (string)$channel->title;
            $feedData->description = (string)$channel->description;
            $feedData->link = (string)$channel->link;

            // Extract items
            if (isset($channel->item)) {
                foreach ($channel->item as $item) {
                    $feedItems[] = [
                        'title' => (string)$item->title,
                        'link' => (string)$item->link,
                        'description' => (string)$item->description,
                        'pubDate' => (string)$item->pubDate,
                        'guid' => (string)$item->guid
                    ];
                }
            }
        }
    } else {
        // If parse failed, try to extract basic info using regex
        echo "XML parsing failed, attempting regex-based extraction...\n";

        // Extract feed title
        if (preg_match('/<title>(.*?)<\/title>/is', $originalXml, $matches)) {
            $feedData->title = strip_tags($matches[1]);
        }

        // Extract feed description
        if (preg_match('/<description>(.*?)<\/description>/is', $originalXml, $matches)) {
            $feedData->description = strip_tags($matches[1]);
        }

        // Extract feed link
        if (preg_match('/<link>(.*?)<\/link>/is', $originalXml, $matches)) {
            $feedData->link = trim($matches[1]);
        }

        // Extract items
        preg_match_all('/<item>(.*?)<\/item>/is', $originalXml, $itemMatches);
        if (!empty($itemMatches[1])) {
            foreach ($itemMatches[1] as $itemXml) {
                $item = [];

                // Extract title
                if (preg_match('/<title>(.*?)<\/title>/is', $itemXml, $matches)) {
                    $item['title'] = strip_tags($matches[1]);
                } else {
                    $item['title'] = 'No Title';
                }

                // Extract link
                if (preg_match('/<link>(.*?)<\/link>/is', $itemXml, $matches)) {
                    $item['link'] = trim($matches[1]);
                } else {
                    $item['link'] = $feedData->link;
                }

                // Extract description
                if (preg_match('/<description>(.*?)<\/description>/is', $itemXml, $matches)) {
                    $item['description'] = $matches[1];
                } else {
                    $item['description'] = 'No description available.';
                }

                // Extract pubDate
                if (preg_match('/<pubDate>(.*?)<\/pubDate>/is', $itemXml, $matches)) {
                    $item['pubDate'] = trim($matches[1]);
                } else {
                    $item['pubDate'] = date(DATE_RFC2822);
                }

                // Extract guid
                if (preg_match('/<guid.*?>(.*?)<\/guid>/is', $itemXml, $matches)) {
                    $item['guid'] = trim($matches[1]);
                } else {
                    $item['guid'] = $item['link'];
                }

                $feedItems[] = $item;
            }
        }
    }

    // If we still have no items, create a placeholder
    if (empty($feedItems)) {
        echo "No items found in feed, creating placeholder...\n";
        $feedItems[] = [
            'title' => 'No content found',
            'link' => $feedData->link,
            'description' => 'The RSS feed could not be properly parsed. Please check the original source.',
            'pubDate' => date(DATE_RFC2822),
            'guid' => uniqid()
        ];
    }

    // Step 3: Generate a new XML with proper structure
    $newXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $newXml .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\"";
    $newXml .= " xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"";
    $newXml .= " xmlns:dc=\"http://purl.org/dc/elements/1.1/\"";
    $newXml .= " xmlns:media=\"http://search.yahoo.com/mrss/\">\n";

    // Channel element
    $newXml .= "  <channel>\n";

    // Basic channel metadata
    $newXml .= "    <title><![CDATA[" . cleanText($feedData->title) . "]]></title>\n";
    $newXml .= "    <link>" . htmlspecialchars($feedData->link) . "</link>\n";
    $newXml .= "    <description><![CDATA[" . cleanText($feedData->description) . "]]></description>\n";

    // Add atom:link
    $newXml .= "    <atom:link href=\"" . htmlspecialchars($feedUrl) . "\" rel=\"self\" type=\"application/rss+xml\" />\n";

    // Add additional channel information
    $newXml .= "    <language>vi</language>\n";
    $newXml .= "    <generator>RSS Feed Generator</generator>\n";
    $newXml .= "    <lastBuildDate>" . date(DATE_RFC2822) . "</lastBuildDate>\n";
    $newXml .= "    <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";

    // Add items to the feed
    foreach ($feedItems as $item) {
        $newXml .= "    <item>\n";

        // Title with CDATA
        $title = cleanText($item['title'] ?? 'No Title');
        $newXml .= "      <title><![CDATA[" . $title . "]]></title>\n";

        // Link
        $link = $item['link'] ?? $feedData->link;
        $newXml .= "      <link>" . htmlspecialchars($link) . "</link>\n";

        // GUID (unique identifier)
        $guid = $item['guid'] ?? $link;
        $isPermaLink = $guid === $link ? "true" : "false";
        $newXml .= "      <guid isPermaLink=\"{$isPermaLink}\">" . htmlspecialchars($guid) . "</guid>\n";

        // Description with CDATA
        $description = cleanText($item['description'] ?? 'No description available.');
        $newXml .= "      <description><![CDATA[" . $description . "]]></description>\n";

        // Publication date
        if (!empty($item['pubDate'])) {
            $newXml .= "      <pubDate>" . $item['pubDate'] . "</pubDate>\n";
        } else {
            $newXml .= "      <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";
        }

        $newXml .= "    </item>\n";
    }

    // Close channel and rss elements
    $newXml .= "  </channel>\n";
    $newXml .= "</rss>";

    // Step 4: Validate the XML
    $isValid = false;
    $errorMessage = "";

    try {
        $dom = new DOMDocument();
        $isValid = $dom->loadXML($newXml);

        if (!$isValid) {
            $errorMessage = "Generated XML is not valid";
        } else {
            $errorMessage = "Generated XML is valid!";
        }
    } catch (Exception $e) {
        $errorMessage = "Error validating XML: " . $e->getMessage();
    }

    echo $errorMessage . "\n";

    // Step 5: Save the fixed XML
    if (!is_dir(dirname($outputFile))) {
        mkdir(dirname($outputFile), 0755, true);
    }

    file_put_contents($outputFile, $newXml);
    echo "Fixed RSS feed saved to: {$outputFile}\n";

    // Show first 200 characters
    echo "\nFirst 200 characters of the fixed feed:\n";
    echo substr($newXml, 0, 200) . "...\n";

    // Provide instructions for use
    echo "\nTo use this feed, replace your current feed with this one, or update your application to serve this file.\n";
    echo "You can access it at: " . str_replace(__DIR__, 'http://your-domain.com', $outputFile) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
