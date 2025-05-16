<?php

// This script directly fetches and fixes an RSS feed's format
// to match the exact structure from rss.app without database access

// Settings - can be changed to any feed URL
$feedUrl = isset($argv[1]) ? $argv[1] : 'http://lst.lat/rss/9';
$outputFile = __DIR__ . '/public/feeds/scraped/fixed_feed_custom.xml';

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
    // Step 1: Fetch the original feed using cURL to avoid redirects issues
    echo "Fetching feed from {$feedUrl}...\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects automatically
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $originalXml = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    echo "HTTP Status: {$httpCode}\n";
    echo "Effective URL: {$effectiveUrl}\n";

    if ($originalXml === false || $httpCode >= 400) {
        throw new Exception("Failed to fetch the feed. HTTP status: {$httpCode}");
    }

    // Check if response is HTML instead of XML (common issue)
    if (stripos($originalXml, '<!DOCTYPE html>') !== false || stripos($originalXml, '<html') !== false) {
        echo "Warning: Response appears to be HTML, not RSS/XML.\n";

        // Create a custom feed with a message about the HTML response
        $feedData = new stdClass();
        $feedData->title = 'Custom Feed';
        $feedData->description = 'Custom RSS feed';
        $feedData->link = $feedUrl;

        $feedItems = [[
            'title' => 'Error: HTML Response',
            'link' => $effectiveUrl,
            'description' => 'The URL returned HTML instead of an RSS feed. This might indicate a login page or error.',
            'pubDate' => date(DATE_RFC2822),
            'guid' => uniqid()
        ]];
    } else {
        // Step 2: Parse the original XML to extract its content
        $feedData = new stdClass();
        $feedData->title = 'Custom Feed';
        $feedData->description = 'Custom RSS feed';
        $feedData->link = $feedUrl;
        $feedItems = [];

        // Try to parse the XML
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
                        $itemData = [
                            'title' => (string)$item->title,
                            'link' => (string)$item->link,
                            'description' => (string)$item->description,
                            'pubDate' => (string)$item->pubDate,
                            'guid' => (string)$item->guid
                        ];

                        // Extract creator if available
                        foreach ($item->children('dc', true) as $key => $value) {
                            if ($key == 'creator') {
                                $itemData['creator'] = (string)$value;
                            }
                        }

                        $feedItems[] = $itemData;
                    }
                }
            }
        } else {
            // If parse failed, try to extract basic info using regex
            echo "XML parsing failed, attempting regex-based extraction...\n";

            if (preg_match('/<title>(.*?)<\/title>/is', $originalXml, $matches)) {
                $feedData->title = strip_tags($matches[1]);
            }

            if (preg_match('/<description>(.*?)<\/description>/is', $originalXml, $matches)) {
                $feedData->description = strip_tags($matches[1]);
            }

            if (preg_match('/<link>(.*?)<\/link>/is', $originalXml, $matches)) {
                $feedData->link = trim($matches[1]);
            }

            preg_match_all('/<item>(.*?)<\/item>/is', $originalXml, $itemMatches);
            if (!empty($itemMatches[1])) {
                foreach ($itemMatches[1] as $itemXml) {
                    $item = [];

                    if (preg_match('/<title>(.*?)<\/title>/is', $itemXml, $matches)) {
                        $item['title'] = strip_tags($matches[1]);
                    } else {
                        $item['title'] = 'No Title';
                    }

                    if (preg_match('/<link>(.*?)<\/link>/is', $itemXml, $matches)) {
                        $item['link'] = trim($matches[1]);
                    } else {
                        $item['link'] = $feedData->link;
                    }

                    if (preg_match('/<description>(.*?)<\/description>/is', $itemXml, $matches)) {
                        $item['description'] = $matches[1];
                    } else {
                        $item['description'] = 'No description available.';
                    }

                    if (preg_match('/<pubDate>(.*?)<\/pubDate>/is', $itemXml, $matches)) {
                        $item['pubDate'] = trim($matches[1]);
                    } else {
                        $item['pubDate'] = date(DATE_RFC2822);
                    }

                    if (preg_match('/<guid.*?>(.*?)<\/guid>/is', $itemXml, $matches)) {
                        $item['guid'] = trim($matches[1]);
                    } else {
                        $item['guid'] = $item['link'];
                    }

                    if (preg_match('/<dc:creator>(.*?)<\/dc:creator>/is', $itemXml, $matches)) {
                        $item['creator'] = trim($matches[1]);
                    }

                    $feedItems[] = $item;
                }
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

    // Add a fallback image only if needed
    $defaultImage = function($feedUrl) {
        // Try to get a favicon from the domain
        $parsedUrl = parse_url($feedUrl);
        if (isset($parsedUrl['host'])) {
            $domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            return $domain . '/favicon.ico';
        }
        return 'https://example.com/favicon.ico'; // Generic fallback
    };

    // Step 3: Generate a new XML with simpler structure for n8n compatibility
    $newXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $newXml .= "<rss version=\"2.0\">\n";

    // Channel element
    $newXml .= "  <channel>\n";

    // Basic channel metadata
    $newXml .= "    <title>" . htmlspecialchars($feedData->title) . "</title>\n";
    $newXml .= "    <link>" . htmlspecialchars($feedData->link) . "</link>\n";
    $newXml .= "    <description>" . htmlspecialchars($feedData->description) . "</description>\n";
    $newXml .= "    <language>vi</language>\n";
    $newXml .= "    <lastBuildDate>" . date(DATE_RFC2822) . "</lastBuildDate>\n";

    // Add items to the feed
    foreach ($feedItems as $item) {
        $newXml .= "    <item>\n";

        // Title
        $title = cleanText($item['title'] ?? 'No Title');
        $newXml .= "      <title>" . htmlspecialchars($title) . "</title>\n";

        // Link
        $link = $item['link'] ?? $feedData->link;
        $newXml .= "      <link>" . htmlspecialchars($link) . "</link>\n";

        // Description - simplified without HTML
        $description = cleanText($item['description'] ?? 'No description available.');
        // Strip HTML tags
        $description = strip_tags($description);
        $newXml .= "      <description>" . htmlspecialchars($description) . "</description>\n";

        // Publication date
        if (!empty($item['pubDate'])) {
            $newXml .= "      <pubDate>" . $item['pubDate'] . "</pubDate>\n";
        } else {
            $newXml .= "      <pubDate>" . date(DATE_RFC2822) . "</pubDate>\n";
        }

        // GUID
        $newXml .= "      <guid>" . htmlspecialchars($link) . "</guid>\n";

        $newXml .= "    </item>\n";
    }

    // Close channel and rss elements
    $newXml .= "  </channel>\n";
    $newXml .= "</rss>\n";

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
    echo "\nTo use this feed, update your routes to serve this file.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
