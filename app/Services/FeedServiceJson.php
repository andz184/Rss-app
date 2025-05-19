<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Feed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeedServiceJson extends FeedService
{
    /**
     * Serve RSS feed as JSON format
     *
     * @param Feed $feed
     * @return array
     */
    public function serveAsJson(Feed $feed): array
    {
        try {
            // Get the feed's articles
            $articles = Article::where('feed_id', $feed->id)
                ->orderBy('date', 'desc')
                ->limit(25)
                ->get();

            // Create the JSON feed structure
            $feedData = [
                'title' => $feed->title,
                'link' => $feed->site_url,
                'description' => $feed->description,
                'language' => $feed->language ?? 'vi',
                'lastBuildDate' => now()->format('D, d M Y H:i:s O'),
                'items' => []
            ];

            // Add articles to the feed
            foreach ($articles as $article) {
                $feedData['items'][] = [
                    'title' => $article->title,
                    'link' => $article->url,
                    'description' => $article->content ? strip_tags($article->content) : 'No description available.',
                    'guid' => $article->guid,
                    'pubDate' => $article->date->format('D, d M Y H:i:s O')
                ];
            }

            return $feedData;
        } catch (\Exception $e) {
            Log::error('Error generating JSON feed: ' . $e->getMessage(), [
                'feed_id' => $feed->id
            ]);

            return [
                'error' => 'Error generating JSON feed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fetch and parse a feed, then return as JSON
     *
     * @param Feed $feed
     * @return array
     */
    public function fetchAndParseAsJson(Feed $feed): array
    {
        // First fetch and parse the feed using the parent method
        $result = $this->fetchAndParse($feed);

        // If there was an error or no change, return the result
        if ($result['status'] !== 'success') {
            return $result;
        }

        // Return the feed as JSON
        return $this->serveAsJson($feed);
    }

    /**
     * Direct conversion from XML to JSON format
     *
     * @param string $xmlContent
     * @return array
     */
    public function convertXmlToJson(string $xmlContent): array
    {
        try {
            $xml = simplexml_load_string($xmlContent);

            if (!$xml || !isset($xml->channel)) {
                return [
                    'error' => 'Invalid XML format'
                ];
            }

            $channel = $xml->channel;
            $feedData = [
                'title' => (string)$channel->title,
                'link' => (string)$channel->link,
                'description' => (string)$channel->description,
                'language' => isset($channel->language) ? (string)$channel->language : 'vi',
                'lastBuildDate' => isset($channel->lastBuildDate) ? (string)$channel->lastBuildDate : now()->format('D, d M Y H:i:s O'),
                'items' => []
            ];

            if (isset($channel->item)) {
                foreach ($channel->item as $item) {
                    $feedData['items'][] = [
                        'title' => (string)$item->title,
                        'link' => (string)$item->link,
                        'description' => isset($item->description) ? (string)$item->description : 'No description available.',
                        'guid' => isset($item->guid) ? (string)$item->guid : (string)$item->link,
                        'pubDate' => isset($item->pubDate) ? (string)$item->pubDate : now()->format('D, d M Y H:i:s O')
                    ];
                }
            }

            return $feedData;
        } catch (\Exception $e) {
            Log::error('Error converting XML to JSON: ' . $e->getMessage());

            return [
                'error' => 'Error converting XML to JSON: ' . $e->getMessage()
            ];
        }
    }
}
