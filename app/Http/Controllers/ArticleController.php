<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    /**
     * Display a listing of all articles.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $perPage = $request->input('per_page', 15);

        $articlesQuery = Article::query()
            ->join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->select('articles.*')
            ->with('feed');

        // Apply filters
        $articlesQuery = match($filter) {
            'unread' => $articlesQuery->where('articles.is_read', false),
            'favorites' => $articlesQuery->where('articles.is_favorite', true),
            default => $articlesQuery
        };

        // Apply category filter if specified
        if ($categoryId = $request->get('category')) {
            $articlesQuery->whereHas('feed', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        // Apply feed filter if specified
        if ($feedId = $request->get('feed')) {
            $articlesQuery->where('feed_id', $feedId);
        }

        // Order by date, newest first
        $articles = $articlesQuery->orderBy('articles.date', 'desc')->paginate($perPage);

        // Get categories and feeds for sidebar
        $categories = Auth::user()->categories()->withCount('feeds')->orderBy('name')->get();
        $feeds = Auth::user()->feeds()
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        return view('articles.index', compact('articles', 'categories', 'feeds', 'filter'));
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article)
    {
        $this->authorize('view', $article);

        // Mark as read when viewed
        if (!$article->is_read) {
            $article->update(['is_read' => true]);
        }

        // Get the next and previous article in the same feed
        $prevArticle = Article::where('feed_id', $article->feed_id)
            ->where('date', '>', $article->date)
            ->orderBy('date', 'asc')
            ->first();

        $nextArticle = Article::where('feed_id', $article->feed_id)
            ->where('date', '<', $article->date)
            ->orderBy('date', 'desc')
            ->first();

        // Get categories and feeds for sidebar
        $categories = Auth::user()->categories()->withCount('feeds')->orderBy('name')->get();
        $feeds = Auth::user()->feeds()
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        return view('articles.show', compact('article', 'prevArticle', 'nextArticle', 'categories', 'feeds'));
    }

    /**
     * Toggle the read status of an article.
     */
    public function toggleRead(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $article->update(['is_read' => !$article->is_read]);

        if ($request->wantsJson()) {
            return response()->json(['is_read' => $article->is_read]);
        }

        return back();
    }

    /**
     * Toggle the favorite status of an article.
     */
    public function toggleFavorite(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $article->update(['is_favorite' => !$article->is_favorite]);

        if ($request->wantsJson()) {
            return response()->json(['is_favorite' => $article->is_favorite]);
        }

        return back();
    }

    /**
     * Mark all articles as read.
     */
    public function markAllRead(Request $request)
    {
        $feedId = $request->input('feed_id');
        $categoryId = $request->input('category_id');

        $query = Article::query()
            ->join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('articles.is_read', false);

        if ($feedId) {
            $query->where('articles.feed_id', $feedId);
        } elseif ($categoryId) {
            $query->whereHas('feed', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $count = $query->count();

        // Update directly via SQL for performance
        Article::whereIn('id', $query->select('articles.id'))->update(['is_read' => true]);

        return redirect()->back()
            ->with('success', "{$count} articles marked as read.");
    }
}
