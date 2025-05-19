<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Services\FeedServiceJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class JsonRssFeedController extends Controller
{
    protected $feedService;

    public function __construct(FeedServiceJson $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Serve RSS feed as JSON format
     *
     * @param int|Feed $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function serve($feed)
    {
        try {
            // If feed parameter is a Feed model instance, use it directly
            if (!($feed instanceof Feed)) {
                // Otherwise find the feed by ID
                $feed = Feed::findOrFail($feed);
            }

            // Get the feed data in JSON format
            $feedData = $this->feedService->serveAsJson($feed);

            // Return JSON response
            return response()->json($feedData);
        } catch (\Exception $e) {
            Log::error('Error serving JSON feed: ' . $e->getMessage(), [
                'feed_id' => $feed->id ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'Error serving RSS feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch and parse a feed, then return as JSON
     *
     * @param int|Feed $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAndServe($feed)
    {
        try {
            // If feed parameter is a Feed model instance, use it directly
            if (!($feed instanceof Feed)) {
                // Otherwise find the feed by ID
                $feed = Feed::findOrFail($feed);
            }

            // Fetch and parse the feed, then get the data in JSON format
            $result = $this->feedService->fetchAndParseAsJson($feed);

            // Return JSON response
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching and serving JSON feed: ' . $e->getMessage(), [
                'feed_id' => $feed->id ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'Error fetching and serving RSS feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert XML feed to JSON format
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convertXmlToJson(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'xml_content' => 'required|string',
            ]);

            // Convert XML to JSON
            $result = $this->feedService->convertXmlToJson($validated['xml_content']);

            // Return JSON response
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error converting XML to JSON: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error converting XML to JSON: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Custom route for the specific feed format requested by user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function customFeed()
    {
        // Example data structure as provided by the user
        $feedData = [
            'title' => 'RSS Feed - www.artificialintelligence-news.com',
            'link' => 'https://www.artificialintelligence-news.com/',
            'description' => 'Generated RSS feed from https://www.artificialintelligence-news.com/',
            'language' => 'vi',
            'lastBuildDate' => date('D, d M Y H:i:s O'),
            'items' => []
        ];

        // Add sample items (in a real application, these would come from the database or an external source)
        $feedData['items'] = [
            [
                'title' => 'The role of machine learning in enhancing cloud-native container security',
                'link' => 'https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/',
                'description' => 'No description available.',
                'guid' => 'https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/',
                'pubDate' => date('D, d M Y H:i:s O')
            ],
            [
                'title' => 'Innovative machine learning uses transforming business applications',
                'link' => 'https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/',
                'description' => 'No description available.',
                'guid' => 'https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/',
                'pubDate' => date('D, d M Y H:i:s O')
            ],
            // Add more items as needed
        ];

        return response()->json($feedData);
    }
}
