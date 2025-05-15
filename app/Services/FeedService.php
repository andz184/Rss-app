<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Feed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeedService
{
    /**
     * Fetch and parse a feed
     *
     * @param Feed $feed
     * @return array
     */
    public function fetchAndParse(Feed $feed): array
    {
        try {
            $headers = [];

            // Add conditional headers if available to leverage HTTP caching
            if ($feed->etag) {
                $headers['If-None-Match'] = $feed->etag;
            }

            if ($feed->last_modified) {
                $headers['If-Modified-Since'] = $feed->last_modified;
            }

            $response = Http::withHeaders($headers)->get($feed->feed_url);

            // If the feed has not been modified, return early
            if ($response->status() === 304) {
                return [
                    'status' => 'not_modified',
                    'new_articles' => 0,
                ];
            }

            // If we get an error status code, log and return
            if ($response->failed()) {
                $feed->error_count += 1;
                $feed->save();

                return [
                    'status' => 'error',
                    'message' => 'HTTP error: ' . $response->status(),
                ];
            }

            // Update etag and last-modified for future requests
            $feed->etag = $response->header('ETag');
            $feed->last_modified = $response->header('Last-Modified');

            // Parse the RSS content
            $xmlContent = $response->body();
            $rss = simplexml_load_string($xmlContent);

            if (!$rss) {
                $feed->error_count += 1;
                $feed->save();

                return [
                    'status' => 'error',
                    'message' => 'Failed to parse XML',
                ];
            }

            $newArticlesCount = $this->processRssItems($feed, $rss);

            // Update feed's last_updated and reset error count
            $feed->last_updated = now();
            $feed->error_count = 0;
            $feed->save();

            return [
                'status' => 'success',
                'new_articles' => $newArticlesCount,
            ];
        } catch (\Exception $e) {
            Log::error('Feed fetch error: ' . $e->getMessage(), [
                'feed_id' => $feed->id,
                'feed_url' => $feed->feed_url,
            ]);

            $feed->error_count += 1;
            $feed->save();

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process RSS items and save new articles
     *
     * @param Feed $feed
     * @param \SimpleXMLElement $rss
     * @return int Number of new articles
     */
    protected function processRssItems(Feed $feed, \SimpleXMLElement $rss): int
    {
        $newCount = 0;

        // Handle different RSS formats
        $items = null;

        // Standard RSS
        if (isset($rss->channel->item)) {
            $items = $rss->channel->item;
        }
        // Atom
        elseif (isset($rss->entry)) {
            $items = $rss->entry;
        }
        // If format not recognized
        else {
            return 0;
        }

        foreach ($items as $item) {
            // Generate a hash for the article to prevent duplicates
            $hash = $this->generateArticleHash($item);

            // Skip if we already have this article
            if (Article::where('feed_id', $feed->id)->where('hash', $hash)->exists()) {
                continue;
            }

            // Create the new article
            $article = new Article();
            $article->fill([
                'guid' => $this->getGuid($item),
                'title' => $this->getTitle($item),
                'author' => $this->getAuthor($item),
                'content' => $this->getContent($item),
                'url' => $this->getLink($item),
                'comments_url' => $this->getCommentsLink($item),
                'date' => $this->getPublishedDate($item),
                'feed_id' => $feed->id,
                'image' => $this->getImage($item),
                'hash' => $hash,
            ]);

            $article->save();
            $newCount++;
        }

        return $newCount;
    }

    /**
     * Extract GUID from item
     */
    protected function getGuid($item): string
    {
        // RSS
        if (isset($item->guid)) {
            return (string)$item->guid;
        }

        // Atom
        if (isset($item->id)) {
            return (string)$item->id;
        }

        // Fallback to link or generate a UUID
        return $this->getLink($item) ?: (string)Str::uuid();
    }

    /**
     * Extract title from item
     */
    protected function getTitle($item): string
    {
        return isset($item->title) ? (string)$item->title : 'Untitled';
    }

    /**
     * Extract author from item
     */
    protected function getAuthor($item): ?string
    {
        // RSS
        if (isset($item->author)) {
            return (string)$item->author;
        }

        // Atom
        if (isset($item->author->name)) {
            return (string)$item->author->name;
        }

        // DC extension
        if (isset($item->children('dc', true)->creator)) {
            return (string)$item->children('dc', true)->creator;
        }

        return null;
    }

    /**
     * Extract content from item
     */
    protected function getContent($item): ?string
    {
        // Check for content:encoded (RSS)
        if (isset($item->children('content', true)->encoded)) {
            return (string)$item->children('content', true)->encoded;
        }

        // Check for description (RSS)
        if (isset($item->description)) {
            return (string)$item->description;
        }

        // Check for content (Atom)
        if (isset($item->content)) {
            return (string)$item->content;
        }

        // Check for summary (Atom)
        if (isset($item->summary)) {
            return (string)$item->summary;
        }

        return null;
    }

    /**
     * Extract link from item
     */
    protected function getLink($item): string
    {
        // RSS
        if (isset($item->link)) {
            return (string)$item->link;
        }

        // Atom
        if (isset($item->link['href'])) {
            return (string)$item->link['href'];
        }

        // Fallback to guid if it's a URL
        if (isset($item->guid) && filter_var((string)$item->guid, FILTER_VALIDATE_URL)) {
            return (string)$item->guid;
        }

        return '';
    }

    /**
     * Extract comments link from item
     */
    protected function getCommentsLink($item): ?string
    {
        // RSS 2.0
        if (isset($item->comments)) {
            return (string)$item->comments;
        }

        // Slash extension
        if (isset($item->children('slash', true)->comments)) {
            return (string)$item->children('slash', true)->comments;
        }

        return null;
    }

    /**
     * Extract published date from item
     */
    protected function getPublishedDate($item): Carbon
    {
        // RSS
        if (isset($item->pubDate)) {
            return Carbon::parse((string)$item->pubDate);
        }

        // Atom
        if (isset($item->published)) {
            return Carbon::parse((string)$item->published);
        }

        // Atom (updated)
        if (isset($item->updated)) {
            return Carbon::parse((string)$item->updated);
        }

        // DC extension
        if (isset($item->children('dc', true)->date)) {
            return Carbon::parse((string)$item->children('dc', true)->date);
        }

        // Default to now
        return Carbon::now();
    }

    /**
     * Extract image from item
     */
    protected function getImage($item): ?string
    {
        // Check for media:content
        if (isset($item->children('media', true)->content)) {
            $media = $item->children('media', true)->content;

            if (isset($media['url']) && $this->isImageType($media['type'] ?? '')) {
                return (string)$media['url'];
            }
        }

        // Check for media:thumbnail
        if (isset($item->children('media', true)->thumbnail)) {
            $thumbnail = $item->children('media', true)->thumbnail;

            if (isset($thumbnail['url'])) {
                return (string)$thumbnail['url'];
            }
        }

        // Try to find image in content
        $content = $this->getContent($item);
        if ($content) {
            if (preg_match('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $content, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Check if the media type is an image
     */
    protected function isImageType(?string $type): bool
    {
        if (!$type) {
            return false;
        }

        return strpos($type, 'image/') === 0;
    }

    /**
     * Generate a unique hash for the article
     */
    protected function generateArticleHash($item): string
    {
        // Combine title, link, guid, and content for a unique hash
        $components = [
            $this->getTitle($item),
            $this->getLink($item),
            $this->getGuid($item),
            $this->getContent($item),
        ];

        $hashString = implode('|', array_filter($components));

        return md5($hashString);
    }
}
