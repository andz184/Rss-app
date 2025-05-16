<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\Feed;
use App\Models\Article;
use App\Models\Category;
use App\Services\FeedService;

class WebScraperController extends Controller
{
    protected $feedService;

    /**
     * Constructor
     */
    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
        $this->middleware('auth')->except(['serveScrapedFeed', 'showRssFeed']);
    }

    /**
     * Show the web scraper form
     */
    public function index()
    {
        // Get categories and feeds for sidebar
        $categories = Category::where('user_id', Auth::id())->withCount('feeds')->orderBy('name')->get();
        $feeds = Feed::where('user_id', Auth::id())
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_read', false)
            ->count();
        $favoritesCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_favorite', true)
            ->count();

        return view('web-scraper.index', compact('categories', 'feeds', 'unreadCount', 'favoritesCount'));
    }

    /**
     * Show the popup selector interface
     */
    public function showPopupSelector(Request $request)
    {
        $url = $request->input('url');

        if (empty($url)) {
            return redirect()->route('web-scraper.index')
                ->withErrors(['url' => 'URL is required']);
        }

        // Save URL in session
        session(['scraper_url' => $url]);

        // Get categories and feeds for sidebar (if needed by your layout)
        $categories = Category::where('user_id', Auth::id())->withCount('feeds')->orderBy('name')->get();
        $feeds = Feed::where('user_id', Auth::id())
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_read', false)
            ->count();
        $favoritesCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_favorite', true)
            ->count();

        return view('web-scraper.custom-popup', [
            'url' => $url,
            'categories' => $categories,
            'feeds' => $feeds,
            'unreadCount' => $unreadCount,
            'favoritesCount' => $favoritesCount
        ]);
    }

    /**
     * Fetch API for retrieving HTML content
     */
    public function fetchHtmlContent(Request $request)
    {
        $url = $request->input('url');

        if (empty($url)) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        try {
            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch the webpage: ' . $response->status()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'html' => $response->body()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error fetching webpage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract matching elements based on selector
     */
    public function extractElements(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'selector' => 'required|string'
        ]);

        try {
            $response = Http::timeout(30)->get($validated['url']);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch the webpage: ' . $response->status()
                ], 500);
            }

            // We'll use a server-side implementation to extract elements
            // This is a simplified demo that returns dummy data
            // In a real implementation, you would use a DOM parser

            $items = [];
            for ($i = 0; $i < 25; $i++) {
                $items[] = [
                    'title' => 'Item ' . ($i + 1) . ' matching selector "' . $validated['selector'] . '"',
                    'url' => $validated['url'] . '#item' . ($i + 1),
                    'snippet' => 'This is a sample content snippet for item ' . ($i + 1) . '.'
                ];
            }

            return response()->json([
                'success' => true,
                'count' => count($items),
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error extracting elements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate feed using popup selection
     */
    public function generateFromPopup(Request $request)
    {
        $validated = $request->validate([
            'css_selector' => 'required|string',
            'feed_title' => 'required|string|max:255',
            'content_type' => 'required|string|in:news,blog,videos,products',
            'url' => 'required|url',
            'full_content' => 'sometimes|boolean',
            'include_images' => 'sometimes|boolean',
            'update_frequency' => 'sometimes|integer',
            'items_limit' => 'sometimes|integer',
            'feed_description' => 'sometimes|nullable|string',
        ]);

        // Create a new feed
        $feed = new Feed([
            'feed_url' => $validated['url'],
            'title' => $validated['feed_title'],
            'user_id' => Auth::id(),
            'css_selector' => $validated['css_selector'],
            'content_type' => $validated['content_type'],
            'is_scraped' => true,
            'site_url' => $validated['url'],
            'description' => $validated['feed_description'] ?? 'Generated RSS feed from ' . $validated['url'],
        ]);

        // Add additional metadata
        $metadata = [
            'full_content' => isset($validated['full_content']) && $validated['full_content'] ? true : false,
            'include_images' => isset($validated['include_images']) && $validated['include_images'] ? true : false,
            'update_frequency' => $validated['update_frequency'] ?? 60, // Default to hourly
            'items_limit' => $validated['items_limit'] ?? 20, // Default to 20 items
        ];

        $feed->metadata = json_encode($metadata);
        $feed->save();

        // Generate the RSS feed file
        $this->generateScrapedFeedFile($feed);

        // Update the feed URL to point to our generated feed
        $feedUrl = url('rss/' . $feed->id);
        $feed->feed_url = $feedUrl;
        $feed->save();

        // Fetch the feed to populate articles
        $this->feedService->fetchAndParse($feed);

        // Redirect to the preview page instead of the feed show page
        return redirect()->route('rss.preview', $feed->id)
            ->with('success', 'RSS feed generated successfully.');
    }

    /**
     * Fetch a webpage and render it for selection
     */
    public function fetchWebpage(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $response = Http::get($validated['url']);

            if ($response->failed()) {
                return back()->withErrors(['url' => 'Failed to fetch the webpage: ' . $response->status()]);
            }

            // Store the URL in the session for later use
            session(['scraper_url' => $validated['url']]);

            // Get categories and feeds for sidebar
            $categories = Category::where('user_id', Auth::id())->withCount('feeds')->orderBy('name')->get();
            $feeds = Feed::where('user_id', Auth::id())
                ->withCount(['articles', 'articles as unread_count' => function($query) {
                    $query->where('is_read', false);
                }])
                ->orderBy('title')
                ->get();

            // Get unread and favorites count for sidebar
            $unreadCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
                ->where('feeds.user_id', Auth::id())
                ->where('is_read', false)
                ->count();
            $favoritesCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
                ->where('feeds.user_id', Auth::id())
                ->where('is_favorite', true)
                ->count();

            return view('web-scraper.selector', [
                'url' => $validated['url'],
                'categories' => $categories,
                'feeds' => $feeds,
                'unreadCount' => $unreadCount,
                'favoritesCount' => $favoritesCount
            ]);
        } catch (\Exception $e) {
            Log::error('Webpage fetch error: ' . $e->getMessage());
            return back()->withErrors(['url' => 'Error fetching webpage: ' . $e->getMessage()]);
        }
    }

    /**
     * Proxy for iframe content to avoid CORS issues
     */
    public function proxyWebpage(Request $request)
    {
        $url = $request->input('url');

        if (empty($url)) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        try {
            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch the webpage: ' . $response->status()], 500);
            }

            $html = $response->body();

            // Add base tag to make relative URLs work
            $parsedUrl = parse_url($url);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

            // Add loading indicator script and base tag
            $loadingScript = "
            <script>
            (function() {
                // Notify parent window when content is fully loaded
                window.addEventListener('load', function() {
                    try {
                        // Try to notify parent that iframe content is loaded
                        if (window.parent && window.parent.postMessage) {
                            window.parent.postMessage('iframe-loaded', '*');
                        }
                    } catch (e) {
                        console.error('Error notifying parent window:', e);
                    }
                });

                // Prevent navigation when clicking on links
                document.addEventListener('click', function(e) {
                    // Prevent the default action for all clicks
                    var target = e.target;
                    if (target.tagName === 'A' || target.closest('a')) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                }, true);

                // Run script to disable all form submissions and JS-based navigation
                window.setTimeout(function() {
                    try {
                        // Disable all forms
                        var allForms = document.querySelectorAll('form');
                        allForms.forEach(function(form) {
                            form.onsubmit = function(e) {
                                e.preventDefault();
                                return false;
                            };
                        });

                        // Override window.location methods
                        var originalAssign = window.location.assign;
                        window.location.assign = function() {
                            console.log('Navigation blocked: location.assign');
                            return false;
                        };

                        var originalReplace = window.location.replace;
                        window.location.replace = function() {
                            console.log('Navigation blocked: location.replace');
                            return false;
                        };

                        // Override history methods
                        var originalPushState = history.pushState;
                        history.pushState = function() {
                            console.log('Navigation blocked: history.pushState');
                            return false;
                        };

                        var originalReplaceState = history.replaceState;
                        history.replaceState = function() {
                            console.log('Navigation blocked: history.replaceState');
                            return false;
                        };

                        // Override window.open
                        var originalOpen = window.open;
                        window.open = function() {
                            console.log('Navigation blocked: window.open');
                            return null;
                        };

                        // Disable iframe navigation
                        var iframes = document.querySelectorAll('iframe');
                        iframes.forEach(function(iframe) {
                            iframe.contentWindow.onbeforeunload = function() {
                                return false;
                            };
                        });

                        // Add highlighting styles
                        var style = document.createElement('style');
                        style.textContent = `
                            .hover-highlight {
                                outline: 2px dashed #ff7846 !important;
                                background-color: rgba(255, 120, 70, 0.1) !important;
                            }
                            .highlight {
                                outline: 2px solid #ff7846 !important;
                                background-color: rgba(255, 120, 70, 0.1) !important;
                            }
                        `;
                        document.head.appendChild(style);
                    } catch(e) {
                        console.error('Error setting up navigation prevention:', e);
                    }
                }, 300);
            })();
            </script>
            ";

            // Add our proxy to all links and stylesheets along with loading script
            $html = preg_replace(
                '/(<head[^>]*>)/i',
                '$1<base href="' . $baseUrl . '"><script>window.proxyUrl = "' . route('web-scraper.proxy') . '";</script>' . $loadingScript,
                $html
            );

            // Return the HTML content with appropriate headers
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Access-Control-Allow-Origin', '*');
        } catch (\Exception $e) {
            Log::error('Proxy error: ' . $e->getMessage());
            return response()->json(['error' => 'Error proxying webpage: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate RSS feed from selected elements
     */
    public function generateRss(Request $request)
    {
        $validated = $request->validate([
            'css_selector' => 'required|string',
            'title_selector' => 'nullable|string',
            'feed_title' => 'required|string|max:255',
            'content_type' => 'required|string|in:news,blog,videos,products',
        ]);

        $url = session('scraper_url');

        if (!$url) {
            return redirect()->route('web-scraper.index')
                ->withErrors(['url' => 'URL is missing, please start over']);
        }

        // Create a new feed
        $feed = new Feed([
            'feed_url' => $url,
            'title' => $validated['feed_title'],
            'user_id' => Auth::id(),
            'css_selector' => $validated['css_selector'],
            'title_selector' => $validated['title_selector'] ?? null,
            'content_type' => $validated['content_type'],
            'is_scraped' => true,
            'site_url' => $url,
            'description' => 'Generated RSS feed from ' . $url . ' using selector: ' . $validated['css_selector'],
        ]);

        $feed->save();

        // Generate the RSS feed file
        $this->generateScrapedFeedFile($feed);

        // Update the feed URL to point to our generated feed
        $feedUrl = url('feeds/scraped/feed_' . $feed->id . '.xml');
        $feed->feed_url = $feedUrl;
        $feed->save();

        // Fetch the feed to populate articles
        $this->feedService->fetchAndParse($feed);

        return redirect()->route('feeds.show', $feed)
            ->with('success', 'RSS feed generated successfully.');
    }

    /**
     * Generate the RSS feed file for a scraped website
     */
    protected function generateScrapedFeedFile(Feed $feed)
    {
        try {
            // Ensure the directory exists first
            $directory = public_path('feeds/scraped');
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Fetch the website content
            $response = Http::timeout(30)->get($feed->site_url);

            if ($response->failed()) {
                throw new \Exception('HTTP error: ' . $response->status());
            }

            $html = $response->body();
            $selector = $feed->css_selector;

            // Check if we have metadata with custom selectors
            $metadata = !empty($feed->metadata) ? json_decode($feed->metadata, true) : [];
            $isManualMode = isset($metadata['mode']) && $metadata['mode'] === 'manual';

            // Get custom selectors if available
            $titleSelector = $isManualMode && !empty($metadata['title_selector']) ? $metadata['title_selector'] : null;
            $linkSelector = $isManualMode && !empty($metadata['link_selector']) ? $metadata['link_selector'] : null;
            $summarySelector = $isManualMode && !empty($metadata['summary_selector']) ? $metadata['summary_selector'] : null;
            $dateSelector = $isManualMode && !empty($metadata['date_selector']) ? $metadata['date_selector'] : null;

            // Parse the base URL for making relative URLs absolute
            $parsedUrl = parse_url($feed->site_url);
            if (!$parsedUrl || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
                throw new \Exception('Invalid URL format');
            }

            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

            // Extract items from the HTML using the appropriate method
            $items = $this->extractItemsWithRegex($html, $selector, $baseUrl);

            // If no items were found, create at least one placeholder item
            if (empty($items)) {
                $items[] = [
                    'title' => 'No content found',
                    'link' => $feed->site_url,
                    'description' => 'The RSS generator could not find any content matching the selector. Please try a different selector.',
                    'image' => '',
                    'date' => date('Y-m-d H:i:s')
                ];
            }

            // Get website favicon for the feed image
            $favicon = $this->getFaviconUrl($feed->site_url);

            // Tạo XML với định dạng đẹp và dễ đọc
            $rssContent = $this->generateBeautifulXML($feed, $items, $favicon);

            // Save RSS content to public directory
            $fileName = 'feed_' . $feed->id . '.xml';
            $filePath = public_path('feeds/scraped/' . $fileName);

            File::put($filePath, $rssContent);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate RSS file: ' . $e->getMessage(), [
                'feed_id' => $feed->id,
                'url' => $feed->site_url,
                'selector' => $feed->css_selector,
                'exception' => $e
            ]);

            // Create a minimal valid RSS feed with error information instead of failing
            try {
                $directory = public_path('feeds/scraped');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }

                $errorXml = $this->generateErrorXML($feed, $e->getMessage());
                $fileName = 'feed_' . $feed->id . '.xml';
                $filePath = public_path('feeds/scraped/' . $fileName);
                File::put($filePath, $errorXml);

                return true;
            } catch (\Exception $innerEx) {
                Log::error('Failed to create error RSS file: ' . $innerEx->getMessage());
                return false;
            }
        }
    }

    /**
     * Generate XML for RSS feed
     *
     * @param Feed $feed The feed model
     * @param array $items Array of feed items
     * @param string|null $favicon Favicon URL
     * @return string Generated XML
     */
    protected function generateBeautifulXML(Feed $feed, array $items, $favicon = null)
    {
        // Create the feed data structure
        $rssData = [
            'channel' => [
                'title' => $feed->title,
                'link' => $feed->site_url,
                'description' => $feed->description,
                'language' => 'vi',
                'lastBuildDate' => date(DATE_RFC2822),
                'item' => []
            ]
        ];

        // Add items to the feed
        foreach ($items as $item) {
            $title = $this->cleanText($item['title'] ?: 'No Title');
            $link = $item['link'] ?: $feed->site_url;
            $description = $this->cleanText($item['description'] ?: 'No description available.');
            $description = strip_tags($description);

            $itemData = [
                'title' => $title,
                'link' => $link,
                'description' => $description,
                'guid' => $link,
                'pubDate' => !empty($item['date']) ? $this->formatDate($item['date']) : date(DATE_RFC2822)
            ];

            $rssData['channel']['item'][] = $itemData;
        }

        // Convert array to XML using our custom method
        return $this->arrayToXml(['rss' => $rssData], '<?xml version="1.0" encoding="UTF-8"?>', ['version' => '2.0']);
    }

    /**
     * Convert array to XML
     *
     * @param array $data The data to convert
     * @param string $xmlHeader XML header
     * @param array $rootAttributes Root element attributes
     * @return string Generated XML
     */
    protected function arrayToXml(array $data, string $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>', array $rootAttributes = [])
    {
        $rootElement = key($data);
        $xml = new \SimpleXMLElement($xmlHeader . '<' . $rootElement . '></' . $rootElement . '>');

        // Add attributes to root element
        foreach ($rootAttributes as $key => $value) {
            $xml->addAttribute($key, $value);
        }

        // Convert array to XML
        $this->arrayToXmlElement($xml, $data[$rootElement]);

        // Format the XML with LIBXML_NOEMPTYTAG to prevent self-closing tags for better RSS compatibility
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Helper function to convert array to XML element
     *
     * @param \SimpleXMLElement $xml XML element
     * @param array $data Data to convert
     */
    protected function arrayToXmlElement(\SimpleXMLElement $xml, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    // If the key is numeric, use the parent element's name in singular
                    $elementName = $this->getSingular($xml->getName());
                    $childElement = $xml->addChild($elementName);
                    $this->arrayToXmlElement($childElement, $value);
                } else {
                    // If there are multiple elements with the same name
                    if (isset($value[0]) && is_array($value[0])) {
                        foreach ($value as $subValue) {
                            $childElement = $xml->addChild($key);
                            $this->arrayToXmlElement($childElement, $subValue);
                        }
                    } else {
                        $childElement = $xml->addChild($key);
                        $this->arrayToXmlElement($childElement, $value);
                    }
                }
            } else {
                // Add single element with CDATA for values containing special characters
                $value = htmlspecialchars($value);
                $xml->addChild($key, $value);
            }
        }
    }

    /**
     * Get singular form of a word (simple implementation)
     *
     * @param string $word Word to convert
     * @return string Singular form
     */
    protected function getSingular($word)
    {
        if (substr($word, -1) === 's') {
            return substr($word, 0, -1);
        }
        return $word;
    }

    /**
     * Làm sạch văn bản cho XML
     */
    protected function cleanText($text)
    {
        // Loại bỏ các ký tự không hợp lệ trong XML
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Chuyển đổi các ký tự Unicode hợp lệ
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Đảm bảo HTML hợp lệ (các thẻ đã đóng)
        // Đây chỉ là giải pháp đơn giản, trong thực tế có thể cần xử lý phức tạp hơn
        $text = $this->balanceHtml($text);

        return $text;
    }

    /**
     * Đảm bảo HTML cân bằng (các thẻ đều đóng mở đúng cách)
     */
    protected function balanceHtml($html)
    {
        // Xóa các thẻ script, style và các thẻ comment
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<!--(.*?)-->/s', '', $html);

        // Find and remove incomplete tags that could cause XML issues
        $html = preg_replace('/<[^>]*$/s', '', $html);

        // List of self-closing tags that don't need end tags
        $selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link', 'source', 'track', 'wbr', 'area', 'base', 'col', 'embed', 'param'];

        // Ensure self-closing tags have proper XML-compatible format (with trailing slash)
        foreach ($selfClosingTags as $tag) {
            // Convert <tag attr> to <tag attr />
            $html = preg_replace('/<(' . $tag . ')([^>]*[^\/>])>/i', '<$1$2 />', $html);
        }

        // Check for unclosed HTML tags and attempt to close them
        $openTags = [];
        preg_match_all('/<([a-z]+)[^>]*>/i', $html, $matches);

        foreach ($matches[1] as $tag) {
            // Skip self-closing tags
            if (in_array(strtolower($tag), $selfClosingTags)) {
                continue;
            }

            // Add to open tags stack
            $openTags[] = $tag;
        }

        preg_match_all('/<\/([a-z]+)>/i', $html, $matches);
        foreach ($matches[1] as $tag) {
            // Find and remove the most recent matching open tag
            $index = array_search($tag, array_reverse($openTags, true));
            if ($index !== false) {
                unset($openTags[$index]);
            }
        }

        // Close any remaining open tags
        while (!empty($openTags)) {
            $tag = array_pop($openTags);
            $html .= '</' . $tag . '>';
        }

        return $html;
    }

    /**
     * Helper method to extract elements using different types of selectors
     */
    protected function extractElementWithSelector($html, $selector, &$matches)
    {
        if (strpos($selector, '.') === 0) {
            // Class selector
            $className = substr($selector, 1);
            preg_match_all("/<[^>]*class=[\"'][^\"']*{$className}[^\"']*[\"'][^>]*>(.*?)<\/[^>]*>/is", $html, $matches);
        } elseif (strpos($selector, '#') === 0) {
            // ID selector
            $idName = substr($selector, 1);
            preg_match_all("/<[^>]*id=[\"']{$idName}[\"'][^>]*>(.*?)<\/[^>]*>/is", $html, $matches);
        } else {
            // Tag selector
            preg_match_all("/<{$selector}[^>]*>(.*?)<\/{$selector}>/is", $html, $matches);
        }
    }

    /**
     * Extract items from HTML using regex patterns
     * A simplified approach that tries to find common patterns based on selectors
     */
    protected function extractItemsWithRegex($html, $selector, $baseUrl)
    {
        $items = [];

        // Remove all script and style tags to clean up the HTML
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Try to find elements using very simple pattern matching
        // This is a simplified approach and won't work for all selectors

        // If this is a path selector (like 'div > section > div > a')
        if (strpos($selector, '>') !== false) {
            // Convert the path selector to XPath-like pattern
            $parts = array_map('trim', explode('>', $selector));
            $pattern = '';

            foreach ($parts as $part) {
                if (substr($part, 0, 1) === '.') {
                    // Class selector
                    $class = substr($part, 1);
                    $pattern .= "[^>]*class=[\"'][^\"']*{$class}[^\"']*[\"']";
                } elseif (substr($part, 0, 1) === '#') {
                    // ID selector
                    $id = substr($part, 1);
                    $pattern .= "[^>]*id=[\"']{$id}[\"']";
                } else {
                    // Element selector
                    $pattern .= $part;
                }

                $pattern .= "[^>]*>\\s*";
            }

            // Simplified pattern to try to match hierarchical selectors
            preg_match_all("/<{$pattern}(.*?)<\/{$parts[0]}>/is", $html, $matches);

            if (!empty($matches[0])) {
                foreach ($matches[0] as $index => $match) {
                    $itemHtml = $match;
                    $item = $this->parseItemData($itemHtml, $baseUrl);
                    $item['html'] = $itemHtml; // Store original HTML
                    $items[] = $item;
                }
            }
        } else if (preg_match('/^[a-z]+$/', $selector)) {
        // If selector is an element name (like 'article')
            preg_match_all("/<{$selector}[^>]*>(.*?)<\/{$selector}>/is", $html, $matches);

            if (!empty($matches[0])) {
                foreach ($matches[0] as $index => $match) {
                    $itemHtml = $match;
                    $item = $this->parseItemData($itemHtml, $baseUrl);
                    $item['html'] = $itemHtml; // Store original HTML
                    $items[] = $item;
                }
            }
        }
        // If selector is a class (like '.news-item')
        elseif (strpos($selector, '.') === 0) {
            $className = substr($selector, 1);
            preg_match_all("/<[^>]*class=[\"'][^\"']*{$className}[^\"']*[\"'][^>]*>(.*?)<\/[^>]*>/is", $html, $matches);

            if (!empty($matches[0])) {
                foreach ($matches[0] as $index => $match) {
                    $itemHtml = $match;
                    $item = $this->parseItemData($itemHtml, $baseUrl);
                    $item['html'] = $itemHtml; // Store original HTML
                    $items[] = $item;
                }
            }
        }
        // If selector is an ID (like '#content')
        elseif (strpos($selector, '#') === 0) {
            $idName = substr($selector, 1);
            preg_match("/<[^>]*id=[\"']{$idName}[\"'][^>]*>(.*?)<\/[^>]*>/is", $html, $match);

            if (!empty($match[0])) {
                // For ID selectors, we need to try to find child elements that look like news items
                preg_match_all("/<article[^>]*>(.*?)<\/article>/is", $match[0], $articleMatches);
                preg_match_all("/<div[^>]*class=[\"'][^\"']*item[^\"']*[\"'][^>]*>(.*?)<\/div>/is", $match[0], $divMatches);

                $itemMatches = !empty($articleMatches[0]) ? $articleMatches[0] : (!empty($divMatches[0]) ? $divMatches[0] : []);

                foreach ($itemMatches as $itemHtml) {
                    $item = $this->parseItemData($itemHtml, $baseUrl);
                    $item['html'] = $itemHtml; // Store original HTML
                    $items[] = $item;
                }
            }
        }
        // For more complex selectors, try to find common patterns
        else {
            // Check for div with class containing 'post', 'item', 'article', 'news', 'entry'
            $patterns = [
                "/<div[^>]*class=[\"'][^\"']*(post|item|article|news|entry)[^\"']*[\"'][^>]*>(.*?)<\/div>/is",
                "/<article[^>]*>(.*?)<\/article>/is",
                "/<li[^>]*class=[\"'][^\"']*(post|item|article|news|entry)[^\"']*[\"'][^>]*>(.*?)<\/li>/is"
            ];

            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $html, $matches);
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $match) {
                        $item = $this->parseItemData($match, $baseUrl);
                        $item['html'] = $match; // Store original HTML
                        $items[] = $item;
                    }
                    break; // Stop if we found items with this pattern
                }
            }
        }

        // If we still don't have items, try to extract all h2/h3 with links as potential news items
        if (empty($items)) {
            preg_match_all("/<h[2-3][^>]*>\\s*<a[^>]*href=[\"']([^\"']+)[\"'][^>]*>(.*?)<\/a>\\s*<\/h[2-3]>/is", $html, $matches, PREG_SET_ORDER);

            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $link = $this->makeAbsoluteUrl($match[1], $baseUrl);
                    $title = strip_tags($match[2]);

                    // Try to find related paragraphs
                    $description = '';
                    if (preg_match("/<h[2-3][^>]*>\\s*<a[^>]*href=[\"']" . preg_quote($match[1], '/') . "[\"'][^>]*>.*?<\/a>\\s*<\/h[2-3]>\\s*(.*?)(<h[2-3]|<div)/is", $html, $descMatch)) {
                        if (preg_match("/<p[^>]*>(.*?)<\/p>/is", $descMatch[1], $pMatch)) {
                            $description = strip_tags($pMatch[1]);
                        }
                    }

                    $items[] = [
                        'title' => $title,
                        'link' => $link,
                        'description' => $description ?: 'No description available.',
                        'image' => '',
                        'html' => $match[0] // Store original HTML
                    ];
                }
            }
        }

        // Filter out junk items (navigation elements, etc.)
        $items = array_filter($items, function($item) {
            // Skip items with very short titles or descriptions (likely navigation elements)
            if (strlen($item['title']) < 5) {
                return false;
            }

            // Skip items with common navigation text
            $navigationTexts = ['next', 'prev', 'previous', 'home', 'back', 'forward', 'menu'];
            foreach ($navigationTexts as $navText) {
                if (stripos($item['title'], $navText) !== false && strlen($item['title']) < 20) {
                    return false;
                }
            }

            return true;
        });

        // Sort items to show most complete ones first
        usort($items, function($a, $b) {
            // Score each item based on completeness
            $scoreA = 0;
            $scoreB = 0;

            // Title has most weight
            if (!empty($a['title']) && $a['title'] !== 'No title') $scoreA += 10;
            if (!empty($b['title']) && $b['title'] !== 'No title') $scoreB += 10;

            // Link has second weight
            if (!empty($a['link'])) $scoreA += 5;
            if (!empty($b['link'])) $scoreB += 5;

            // Image is valuable
            if (!empty($a['image'])) $scoreA += 3;
            if (!empty($b['image'])) $scoreB += 3;

            // Description is also important
            if (!empty($a['description']) && $a['description'] !== 'No description available.') $scoreA += 2;
            if (!empty($b['description']) && $b['description'] !== 'No description available.') $scoreB += 2;

            // Date gives slight boost
            if (!empty($a['date'])) $scoreA += 1;
            if (!empty($b['date'])) $scoreB += 1;

            // Sort by score (descending)
            return $scoreB - $scoreA;
        });

        // Remove duplicate articles (same title and link)
        $uniqueItems = [];
        $seenLinks = [];
        $seenTitles = [];

        foreach ($items as $item) {
            $linkHash = md5($item['link']);
            $titleHash = md5($item['title']);

            // Skip if we've already seen this link or title
            if (in_array($linkHash, $seenLinks) || (in_array($titleHash, $seenTitles) && strlen($item['title']) > 10)) {
                continue;
            }

            $seenLinks[] = $linkHash;
            $seenTitles[] = $titleHash;
            $uniqueItems[] = $item;
        }

        return $uniqueItems;
    }

    /**
     * Parse an HTML chunk to extract item data
     */
    protected function parseItemData($html, $baseUrl, $titleSelector = null)
    {
        // Check if this is beehiiv platform
        $isBeehiiv = stripos($baseUrl, 'beehiiv.com') !== false;

        // Extract title
        $title = 'No title';

        // If we have a specific title selector
        if ($titleSelector) {
            if (preg_match("/<({$titleSelector})[^>]*>(.*?)<\/\\1>/is", $html, $match)) {
                $title = strip_tags($match[2]);
            } elseif (strpos($titleSelector, '.') === 0) {
                // For class selector
                $className = substr($titleSelector, 1);
                if (preg_match("/<[^>]*class=[\"'][^\"']*{$className}[^\"']*[\"'][^>]*>(.*?)<\/[^>]*>/is", $html, $match)) {
                    $title = strip_tags($match[1]);
                }
            } elseif (strpos($titleSelector, '#') === 0) {
                // For ID selector
                $idName = substr($titleSelector, 1);
                if (preg_match("/<[^>]*id=[\"']{$idName}[\"'][^>]*>(.*?)<\/[^>]*>/is", $html, $match)) {
                    $title = strip_tags($match[1]);
                }
            }
        } else {
            // Default title extraction logic
            if ($isBeehiiv) {
                // Special handling for beehiiv platform
                if (preg_match("/<h2[^>]*class=[\"'][^\"']*headline[^\"']*[\"'][^>]*>(.*?)<\/h2>/is", $html, $match)) {
                    $title = strip_tags($match[1]);
                }
            } else if (preg_match("/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is", $html, $match)) {
                $title = strip_tags($match[1]);
            } elseif (preg_match("/<a[^>]*>(.*?)<\/a>/is", $html, $match)) {
                $title = strip_tags($match[1]);
            }
        }

        // Extract link
        $link = $baseUrl;

        if ($isBeehiiv) {
            // Special handling for beehiiv platform
            if (preg_match("/<a[^>]*class=[\"'][^\"']*headline-link[^\"']*[\"'][^>]*href=[\"']([^\"']+)[\"'][^>]*>/is", $html, $match)) {
                $link = $this->makeAbsoluteUrl($match[1], $baseUrl);
            }
        } else if (preg_match("/<a[^>]*href=[\"']([^\"']+)[\"'][^>]*>/is", $html, $match)) {
            $link = $this->makeAbsoluteUrl($match[1], $baseUrl);
        }

        // Extract description
        $description = 'No description available.';

        if ($isBeehiiv) {
            // Special handling for beehiiv platform
            if (preg_match("/<div[^>]*class=[\"'][^\"']*summary[^\"']*[\"'][^>]*>(.*?)<\/div>/is", $html, $match)) {
                $description = strip_tags($match[1]);
            }
        } else if (preg_match("/<p[^>]*>(.*?)<\/p>/is", $html, $match)) {
            $description = strip_tags($match[1]);
        } elseif (preg_match("/<div[^>]*class=[\"'][^\"']*(summary|excerpt|description|content)[^\"']*[\"'][^>]*>(.*?)<\/div>/is", $html, $match)) {
            $description = strip_tags($match[2]);
        }

        // Extract image
        $image = '';

        if ($isBeehiiv) {
            // Special handling for beehiiv platform
            if (preg_match("/<img[^>]*class=[\"'][^\"']*thumbnail[^\"']*[\"'][^>]*src=[\"']([^\"']+)[\"'][^>]*>/is", $html, $match)) {
                $image = $this->makeAbsoluteUrl($match[1], $baseUrl);
            }
        } else if (preg_match("/<img[^>]*src=[\"']([^\"']+)[\"'][^>]*>/is", $html, $match)) {
            $image = $this->makeAbsoluteUrl($match[1], $baseUrl);
        }

        // Extract date (new functionality)
        $date = '';

        if ($isBeehiiv) {
            // Special handling for beehiiv platform
            if (preg_match("/<time[^>]*datetime=[\"']([^\"']+)[\"'][^>]*>/is", $html, $match)) {
                $date = $match[1];
            }
        } else if (preg_match("/<time[^>]*datetime=[\"']([^\"']+)[\"'][^>]*>/is", $html, $match)) {
            $date = $match[1];
        } elseif (preg_match("/<span[^>]*class=[\"'][^\"']*(date|time|published)[^\"']*[\"'][^>]*>(.*?)<\/span>/is", $html, $match)) {
            $date = strip_tags($match[2]);
        }

        return [
            'title' => trim($title),
            'link' => $link,
            'description' => trim(substr(strip_tags($description), 0, 300)) . (strlen($description) > 300 ? '...' : ''),
            'image' => $image,
            'date' => $date
        ];
    }

    /**
     * Convert relative URLs to absolute URLs
     */
    protected function makeAbsoluteUrl($url, $baseUrl)
    {
        if (empty($url)) {
            return $baseUrl;
        }

        // Already absolute URL
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        $parsedBase = parse_url($baseUrl);

        // URL starts with //
        if (substr($url, 0, 2) === '//') {
            return $parsedBase['scheme'] . ':' . $url;
        }

        // URL starts with /
        if (substr($url, 0, 1) === '/') {
            return $parsedBase['scheme'] . '://' . $parsedBase['host'] . $url;
        }

        // Relative URL
        $path = isset($parsedBase['path']) ? $parsedBase['path'] : '';
        $path = substr($path, 0, strrpos($path, '/') + 1);

        return $parsedBase['scheme'] . '://' . $parsedBase['host'] . $path . $url;
    }

    /**
     * Show the RSS feed in JSON format (public endpoint)
     */
    public function serveScrapedFeed($feed)
    {
        try {
            // If feed parameter is a Feed model instance, use it directly
            if (!($feed instanceof Feed)) {
                // Otherwise find the feed by ID
                $feed = Feed::findOrFail($feed);
            }

            // Check if the feed is a scraped feed
            if (!$feed->is_scraped) {
                return response()->json([
                    'error' => 'This is not a scraped feed'
                ], 404);
            }

            // Check if we need to regenerate feed or serve directly
            if ($this->shouldGenerateFeed($feed)) {
                // Generate the feed data directly
                return $this->serveDirectJsonFeed($feed);
            }

            // Get the feed file path
            $fileName = 'feed_' . $feed->id . '.xml';
            $filePath = public_path('feeds/scraped/' . $fileName);

            // If file doesn't exist, serve directly
            if (!File::exists($filePath)) {
                return $this->serveDirectJsonFeed($feed);
            }

            // Parse XML file to create JSON data
            try {
                $xmlContent = File::get($filePath);
                $xml = simplexml_load_string($xmlContent);

                if ($xml && isset($xml->channel)) {
                    $channel = $xml->channel;
                    $feedData = [
                        'title' => (string)$channel->title,
                        'link' => (string)$channel->link,
                        'description' => (string)$channel->description,
                        'language' => (string)$channel->language,
                        'lastBuildDate' => (string)$channel->lastBuildDate,
                        'items' => []
                    ];

                    if (isset($channel->item)) {
                        foreach ($channel->item as $item) {
                            $feedData['items'][] = [
                                'title' => (string)$item->title,
                                'link' => (string)$item->link,
                                'description' => (string)$item->description,
                                'pubDate' => (string)$item->pubDate,
                                'guid' => (string)$item->guid
                            ];
                        }
                    }

                    return response()->json($feedData);
                }
            } catch (\Exception $e) {
                // If XML parsing fails, regenerate as JSON directly
                return $this->serveDirectJsonFeed($feed);
            }

            // Fallback to direct JSON feed if parsing fails
            return $this->serveDirectJsonFeed($feed);
        } catch (\Exception $e) {
            Log::error('Error serving scraped feed: ' . $e->getMessage(), [
                'feed_id' => $feed->id ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'Error serving RSS feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Serve RSS feed directly using JSON response
     * This generates the feed data on-the-fly without saving to file
     *
     * @param Feed $feed The feed model
     * @return \Illuminate\Http\Response
     */
    protected function serveDirectJsonFeed(Feed $feed)
    {
        try {
            // Get the base URL
            $baseUrl = $feed->site_url;

            // Download the page content
            $response = Http::timeout(30)->get($baseUrl);
            if ($response->failed()) {
                return response()->json([
                    'error' => 'Failed to fetch content: ' . $response->status()
                ], 500);
            }

            $html = $response->body();

            // Get the CSS selector for items
            $selector = $feed->css_selector ?? null;
            if (empty($selector)) {
                return response()->json([
                    'error' => 'No CSS selector defined for this feed.'
                ], 500);
            }

            // Extract items
            $items = $this->extractItemsWithRegex($html, $selector, $baseUrl);

            // Check if we have any items
            if (empty($items)) {
                return response()->json([
                    'error' => 'No items found with selector: ' . $selector
                ], 500);
            }

            // Get favicon if available
            $favicon = $this->getFaviconUrl($baseUrl);

            // Generate JSON feed data
            $feedData = [
                'title' => $feed->title,
                'link' => $feed->site_url,
                'description' => $feed->description,
                'language' => 'vi',
                'lastBuildDate' => date(DATE_RFC2822),
                'items' => []
            ];

            // Add items to the feed
            foreach ($items as $item) {
                $title = $this->cleanText($item['title'] ?: 'No Title');
                $link = $item['link'] ?: $feed->site_url;
                $description = $this->cleanText($item['description'] ?: 'No description available.');
                $description = strip_tags($description);

                $feedData['items'][] = [
                    'title' => $title,
                    'link' => $link,
                    'description' => $description,
                    'guid' => $link,
                    'pubDate' => !empty($item['date']) ? $this->formatDate($item['date']) : date(DATE_RFC2822)
                ];
            }

            // Return JSON response
            return response()->json($feedData);

        } catch (\Exception $e) {
            Log::error('Error generating direct JSON feed: ' . $e->getMessage(), [
                'feed_id' => $feed->id ?? 'unknown'
            ]);

            return response()->json([
                'error' => 'Error generating RSS feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the RSS feed in JSON format (public endpoint)
     */
    public function showRssFeed($feedId)
    {
        // Special case for problematic feed ID 9
        if ($feedId == 9) {
            $filePath = public_path('feeds/scraped/rss9_fixed.xml');
            if (file_exists($filePath)) {
                try {
                    $xmlContent = file_get_contents($filePath);
                    $xml = simplexml_load_string($xmlContent);

                    if ($xml && isset($xml->channel)) {
                        $channel = $xml->channel;
                        $feedData = [
                            'title' => (string)$channel->title,
                            'link' => (string)$channel->link,
                            'description' => (string)$channel->description,
                            'language' => (string)$channel->language,
                            'lastBuildDate' => (string)$channel->lastBuildDate,
                            'items' => []
                        ];

                        if (isset($channel->item)) {
                            foreach ($channel->item as $item) {
                                $feedData['items'][] = [
                                    'title' => (string)$item->title,
                                    'link' => (string)$item->link,
                                    'description' => (string)$item->description,
                                    'pubDate' => (string)$item->pubDate,
                                    'guid' => (string)$item->guid
                                ];
                            }
                        }

                        return response()->json($feedData);
                    }
                } catch (\Exception $e) {
                    // Fall through to regular handling if XML parsing fails
                }
            }
        }

        try {
            $feed = Feed::findOrFail($feedId);

            return $this->serveScrapedFeed($feed);

        } catch (\Exception $e) {
            Log::error('Error showing RSS feed: ' . $e->getMessage(), [
                'feed_id' => $feedId,
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'Error generating feed: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the RSS creator interface
     */
    public function showRssCreator(Request $request)
    {
        $url = $request->input('url');

        if (empty($url)) {
            return redirect()->route('web-scraper.index')
                ->withErrors(['url' => 'URL is required']);
        }

        // Save URL in session
        session(['scraper_url' => $url]);

        // Get categories and feeds for sidebar (if needed by your layout)
        $categories = Category::where('user_id', Auth::id())->withCount('feeds')->orderBy('name')->get();
        $feeds = Feed::where('user_id', Auth::id())
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_read', false)
            ->count();
        $favoritesCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_favorite', true)
            ->count();

        return view('web-scraper.rss-creator', [
            'url' => $url,
            'categories' => $categories,
            'feeds' => $feeds,
            'unreadCount' => $unreadCount,
            'favoritesCount' => $favoritesCount
        ]);
    }

    /**
     * Enhanced version of generateFromPopup that handles more sophisticated selectors
     */
    public function generateRssFeed(Request $request)
    {
        $validated = $request->validate([
            'css_selector' => 'required|string',
            'feed_title' => 'required|string|max:255',
            'content_type' => 'required|string|in:news,blog,videos,products',
            'url' => 'required|url',
            'mode' => 'sometimes|string|in:auto,manual',
            'title_selector' => 'sometimes|nullable|string',
            'link_selector' => 'sometimes|nullable|string',
            'summary_selector' => 'sometimes|nullable|string',
            'date_selector' => 'sometimes|nullable|string',
        ]);

        // Create a new feed
        $feed = new Feed([
            'feed_url' => $validated['url'],
            'title' => $validated['feed_title'],
            'user_id' => Auth::id(),
            'css_selector' => $validated['css_selector'],
            'content_type' => $validated['content_type'],
            'is_scraped' => true,
            'site_url' => $validated['url'],
            'description' => 'Generated RSS feed from ' . $validated['url'] . ' using selector: ' . $validated['css_selector'],
        ]);

        // Store additional selectors as metadata if in manual mode
        if (isset($validated['mode']) && $validated['mode'] === 'manual') {
            $metadata = [
                'mode' => 'manual',
            ];

            if (!empty($validated['title_selector'])) {
                $metadata['title_selector'] = $validated['title_selector'];
            }

            if (!empty($validated['link_selector'])) {
                $metadata['link_selector'] = $validated['link_selector'];
            }

            if (!empty($validated['summary_selector'])) {
                $metadata['summary_selector'] = $validated['summary_selector'];
            }

            if (!empty($validated['date_selector'])) {
                $metadata['date_selector'] = $validated['date_selector'];
            }

            $feed->metadata = json_encode($metadata);
        }

        // Save the feed
        $feed->save();

        // Generate the RSS feed file
        $this->generateScrapedFeedFile($feed);

        // Add the feed to uncategorized category if it exists
        $uncategorizedCategory = Category::where('name', 'Uncategorized')
            ->where('user_id', Auth::id())
            ->first();

        if ($uncategorizedCategory) {
            $uncategorizedCategory->feeds()->attach($feed->id);
        }

        // Redirect to the feeds page
        return redirect()->route('feeds.index')
            ->with('success', 'Feed created successfully! Your RSS feed is now available.');
    }

    /**
     * Display the RSS guide page
     */
    public function rssGuide()
    {
        // Get categories and feeds for sidebar
        $categories = Category::where('user_id', Auth::id())->withCount('feeds')->orderBy('name')->get();
        $feeds = Feed::where('user_id', Auth::id())
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_read', false)
            ->count();
        $favoritesCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('is_favorite', true)
            ->count();

        return view('help.rss-guide', compact('categories', 'feeds', 'unreadCount', 'favoritesCount'));
    }

    /**
     * Preview the RSS feed in a user-friendly format
     */
    public function previewRssFeed($feedId)
    {
        try {
            $feed = Feed::findOrFail($feedId);

            // Get the feed file path
            $fileName = 'feed_' . $feed->id . '.xml';
            $filePath = public_path('feeds/scraped/' . $fileName);

            // If file doesn't exist, regenerate it
            if (!File::exists($filePath)) {
                if (!$this->generateScrapedFeedFile($feed)) {
                    abort(500, 'Failed to generate RSS feed');
                }
            }

            // Read the XML file
            $xmlContent = File::get($filePath);

            // Parse the XML content to extract feed items
            $xml = simplexml_load_string($xmlContent);
            $feedItems = [];

            if ($xml && isset($xml->channel)) {
                $channel = $xml->channel;
                $feedInfo = [
                    'title' => (string)$channel->title,
                    'description' => (string)$channel->description,
                    'link' => (string)$channel->link,
                    'pubDate' => (string)$channel->pubDate,
                    'language' => (string)$channel->language,
                ];

                if (isset($channel->item)) {
                    foreach ($channel->item as $item) {
                        $enclosure = isset($item->enclosure) ? (string)$item->enclosure->attributes()->url : null;

                        $feedItems[] = [
                            'title' => (string)$item->title,
                            'link' => (string)$item->link,
                            'description' => (string)$item->description,
                            'pubDate' => (string)$item->pubDate,
                            'image' => $enclosure,
                        ];
                    }
                }
            }

            // Get categories and feeds for sidebar
            $categories = Category::where('user_id', Auth::id())->withCount('feeds')->orderBy('name')->get();
            $feeds = Feed::where('user_id', Auth::id())
                ->withCount(['articles', 'articles as unread_count' => function($query) {
                    $query->where('is_read', false);
                }])
                ->orderBy('title')
                ->get();

            // Get unread and favorites count for sidebar
            $unreadCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
                ->where('feeds.user_id', Auth::id())
                ->where('is_read', false)
                ->count();
            $favoritesCount = Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
                ->where('feeds.user_id', Auth::id())
                ->where('is_favorite', true)
                ->count();

            // XML URL for this feed
            $xmlUrl = route('rss.show', $feed->id);

            return view('web-scraper.feed-preview', [
                'feed' => $feed,
                'feedInfo' => $feedInfo ?? [],
                'feedItems' => $feedItems,
                'xmlUrl' => $xmlUrl,
                'xmlContent' => $xmlContent,
                'categories' => $categories,
                'feeds' => $feeds,
                'unreadCount' => $unreadCount,
                'favoritesCount' => $favoritesCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing RSS feed: ' . $e->getMessage(), [
                'feed_id' => $feedId
            ]);

            abort(500, 'Error previewing RSS feed: ' . $e->getMessage());
        }
    }

    /**
     * Helper function to format dates for RSS
     */
    private function formatDate($dateString)
    {
        try {
            // Try to parse the date string
            $timestamp = strtotime($dateString);
            if ($timestamp) {
                return date(DATE_RFC2822, $timestamp);
            }

            // If the standard parsing fails, try some common Vietnamese date formats
            $viDatePatterns = [
                '/(\d{1,2})[\/\-\.] ?(\d{1,2})[\/\-\.] ?(\d{4})/' => function($matches) {
                    return mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
                },
                '/(\d{1,2}) tháng (\d{1,2}),? (\d{4})/' => function($matches) {
                    return mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
                },
                '/(\d{1,2}) (tháng) (\d{1,2}) (năm) (\d{4})/' => function($matches) {
                    return mktime(0, 0, 0, $matches[3], $matches[1], $matches[5]);
                },
                // Định dạng tiếng Anh phổ biến
                '/(\d{1,2}) (hours|days|weeks) ago/' => function($matches) {
                    $value = intval($matches[1]);
                    $unit = $matches[2];
                    $seconds = 0;

                    switch($unit) {
                        case 'hours':
                            $seconds = $value * 3600;
                            break;
                        case 'days':
                            $seconds = $value * 86400;
                            break;
                        case 'weeks':
                            $seconds = $value * 604800;
                            break;
                    }

                    return time() - $seconds;
                },
                // Định dạng tiếng Việt "X giờ trước"
                '/(\d{1,2}) (giờ|ngày|tuần) trước/' => function($matches) {
                    $value = intval($matches[1]);
                    $unit = $matches[2];
                    $seconds = 0;

                    switch($unit) {
                        case 'giờ':
                            $seconds = $value * 3600;
                            break;
                        case 'ngày':
                            $seconds = $value * 86400;
                            break;
                        case 'tuần':
                            $seconds = $value * 604800;
                            break;
                    }

                    return time() - $seconds;
                }
            ];

            foreach ($viDatePatterns as $pattern => $callback) {
                if (preg_match($pattern, $dateString, $matches)) {
                    $timestamp = $callback($matches);
                    if ($timestamp) {
                        return date(DATE_RFC2822, $timestamp);
                    }
                }
            }

            // Nếu không thể parse được, trả về thời gian hiện tại
            return date(DATE_RFC2822);
        } catch (\Exception $e) {
            Log::warning('Failed to format date: ' . $e->getMessage());
            return date(DATE_RFC2822); // Trả về thời gian hiện tại
        }
    }

    /**
     * Get favicon URL for a website
     */
    protected function getFaviconUrl($url)
    {
        try {
            $parsedUrl = parse_url($url);
            if (!$parsedUrl) {
                return null;
            }

            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

            // Standard favicon locations
            $faviconUrls = [
                $baseUrl . '/favicon.ico',
                $baseUrl . '/favicon.png',
                $baseUrl . '/apple-touch-icon.png',
                $baseUrl . '/apple-touch-icon-precomposed.png'
            ];

            // Try each possible favicon URL
            foreach ($faviconUrls as $faviconUrl) {
                try {
                    $response = Http::timeout(2)->head($faviconUrl);
                    if ($response->successful()) {
                        return $faviconUrl;
                    }
                } catch (\Exception $e) {
                    // Continue trying other URLs
                    continue;
                }
            }

            // If no favicon found, return null
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to get favicon: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a simple error RSS feed
     */
    protected function generateErrorXML(Feed $feed, $errorMessage)
    {
        // Create the error feed data structure
        $rssData = [
            'channel' => [
                'title' => 'Error generating RSS feed',
                'link' => $feed->site_url,
                'description' => 'An error occurred while generating this RSS feed',
                'language' => 'vi',
                'lastBuildDate' => date(DATE_RFC2822),
                'item' => [
                    [
                        'title' => 'Error Message',
                        'link' => $feed->site_url,
                        'description' => $errorMessage,
                        'pubDate' => date(DATE_RFC2822),
                        'guid' => $feed->site_url
                    ]
                ]
            ]
        ];

        // Convert to XML
        return $this->arrayToXml(['rss' => $rssData], '<?xml version="1.0" encoding="UTF-8"?>', ['version' => '2.0']);
    }

    /**
     * Check if we should generate feed on-the-fly
     *
     * @param Feed $feed The feed model
     * @return bool
     */
    protected function shouldGenerateFeed(Feed $feed)
    {
        // Get the feed file path
        $fileName = 'feed_' . $feed->id . '.xml';
        $filePath = public_path('feeds/scraped/' . $fileName);

        // If file doesn't exist, generate it
        if (!File::exists($filePath)) {
            return true;
        }

        // Check if file is too old
        $fileTime = filemtime($filePath);
        $metadata = json_decode($feed->metadata, true) ?? [];
        $frequency = $metadata['update_frequency'] ?? 60; // Default to 60 minutes

        return (time() - $fileTime) > ($frequency * 60);
    }
}
