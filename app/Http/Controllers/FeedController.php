<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FeedController extends Controller
{
    protected FeedService $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    /**
     * Display a listing of the feeds.
     */
    public function index()
    {
        $feeds = Auth::user()->feeds()
            ->with('category')
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        $categories = Auth::user()->categories()->withCount('feeds')->orderBy('name')->get();

        return view('feeds.index', compact('feeds', 'categories'));
    }

    /**
     * Show the form for creating a new feed.
     */
    public function create()
    {
        $categories = Auth::user()->categories()->orderBy('name')->get();
        $feeds = Auth::user()->feeds()
            ->with('category')
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        return view('feeds.create', compact('categories', 'feeds'));
    }

    /**
     * Store a newly created feed in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'feed_url' => [
                'required',
                'url',
                Rule::unique('feeds')
                    ->where(fn ($query) => $query->where('user_id', Auth::id()))
            ],
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Set default values for a new feed
        $feed = new Feed([
            'feed_url' => $validated['feed_url'],
            'title' => 'Loading...',
            'category_id' => $validated['category_id'],
            'user_id' => Auth::id(),
        ]);

        $feed->save();

        // Fetch the feed to get its details
        $result = $this->feedService->fetchAndParse($feed);

        if ($result['status'] !== 'success') {
            $feed->delete();

            return redirect()->route('feeds.create')
                ->withInput()
                ->withErrors(['feed_url' => 'Failed to fetch feed: ' . ($result['message'] ?? 'Unknown error')]);
        }

        return redirect()->route('feeds.index')
            ->with('success', 'Feed added successfully.');
    }

    /**
     * Display the specified feed's articles.
     */
    public function show(Feed $feed, Request $request)
    {
        $this->authorize('view', $feed);

        $perPage = $request->input('per_page', 15);

        $articles = $feed->articles()
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        $categories = Auth::user()->categories()->withCount('feeds')->orderBy('name')->get();
        $feeds = Auth::user()->feeds()
            ->with('category')
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        return view('feeds.show', compact('feed', 'articles', 'categories', 'feeds'));
    }

    /**
     * Show the form for editing the specified feed.
     */
    public function edit(Feed $feed)
    {
        $this->authorize('update', $feed);

        $categories = Auth::user()->categories()->orderBy('name')->get();
        $feeds = Auth::user()->feeds()
            ->with('category')
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        return view('feeds.edit', compact('feed', 'categories', 'feeds'));
    }

    /**
     * Update the specified feed in storage.
     */
    public function update(Request $request, Feed $feed)
    {
        $this->authorize('update', $feed);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'feed_url' => [
                'required',
                'url',
                Rule::unique('feeds')
                    ->where(fn ($query) => $query->where('user_id', Auth::id()))
                    ->ignore($feed->id)
            ],
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $feed->update($validated);

        return redirect()->route('feeds.index')
            ->with('success', 'Feed updated successfully.');
    }

    /**
     * Remove the specified feed from storage.
     */
    public function destroy(Feed $feed)
    {
        $this->authorize('delete', $feed);

        $feed->delete();

        return redirect()->route('feeds.index')
            ->with('success', 'Feed deleted successfully.');
    }

    /**
     * Refresh a feed to fetch new articles.
     */
    public function refresh(Feed $feed)
    {
        $this->authorize('update', $feed);

        $result = $this->feedService->fetchAndParse($feed);

        $message = match($result['status']) {
            'success' => "Feed refreshed successfully. {$result['new_articles']} new articles found.",
            'not_modified' => "Feed has not been modified since last check.",
            default => "Error refreshing feed: {$result['message']}",
        };

        $status = $result['status'] === 'error' ? 'error' : 'success';

        return redirect()->route('feeds.show', $feed)
            ->with($status, $message);
    }

    /**
     * Mark all articles in a feed as read.
     */
    public function markAllRead(Feed $feed)
    {
        $this->authorize('update', $feed);

        $feed->articles()->update(['is_read' => true]);

        return redirect()->route('feeds.show', $feed)
            ->with('success', 'All articles marked as read.');
    }
}
