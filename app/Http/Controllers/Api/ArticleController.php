<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Lấy danh sách tất cả bài viết với các bộ lọc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'nullable|in:all,unread,favorites',
            'category_id' => 'nullable|exists:categories,id',
            'feed_id' => 'nullable|exists:feeds,id',
            'sort' => 'nullable|in:newest,oldest',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $filter = $request->get('filter', 'all');
        $perPage = $request->get('per_page', 15);

        $articlesQuery = Article::query()
            ->join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->select('articles.*')
            ->with('feed.category');

        // Apply filters
        $articlesQuery = match($filter) {
            'unread' => $articlesQuery->where('articles.is_read', false),
            'favorites' => $articlesQuery->where('articles.is_favorite', true),
            default => $articlesQuery
        };

        // Apply category filter if specified
        if ($categoryId = $request->get('category_id')) {
            $articlesQuery->whereHas('feed', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        // Apply feed filter if specified
        if ($feedId = $request->get('feed_id')) {
            $articlesQuery->where('feed_id', $feedId);
        }

        // Apply sorting
        $sort = $request->get('sort', 'newest');
        $articlesQuery->orderBy('articles.date', $sort === 'oldest' ? 'asc' : 'desc');

        // Paginate results
        $articles = $articlesQuery->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    }

    /**
     * Lấy thông tin chi tiết một bài viết.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Article $article)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $article->feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Đánh dấu là đã đọc khi xem
        if (!$article->is_read) {
            $article->update(['is_read' => true]);
        }

        // Lấy bài viết trước và sau của cùng feed
        $prevArticle = Article::where('feed_id', $article->feed_id)
            ->where('date', '>', $article->date)
            ->orderBy('date', 'asc')
            ->first();

        $nextArticle = Article::where('feed_id', $article->feed_id)
            ->where('date', '<', $article->date)
            ->orderBy('date', 'desc')
            ->first();

        $article->load('feed.category');

        return response()->json([
            'status' => 'success',
            'data' => [
                'article' => $article,
                'prev_article' => $prevArticle ? [
                    'id' => $prevArticle->id,
                    'title' => $prevArticle->title
                ] : null,
                'next_article' => $nextArticle ? [
                    'id' => $nextArticle->id,
                    'title' => $nextArticle->title
                ] : null,
            ]
        ]);
    }

    /**
     * Đánh dấu bài viết đã đọc/chưa đọc.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleRead(Article $article)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $article->feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $article->update(['is_read' => !$article->is_read]);

        return response()->json([
            'status' => 'success',
            'message' => $article->is_read ? 'Article marked as read' : 'Article marked as unread',
            'data' => [
                'is_read' => $article->is_read
            ]
        ]);
    }

    /**
     * Đánh dấu bài viết yêu thích/bỏ yêu thích.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleFavorite(Article $article)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $article->feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $article->update(['is_favorite' => !$article->is_favorite]);

        return response()->json([
            'status' => 'success',
            'message' => $article->is_favorite ? 'Article marked as favorite' : 'Article removed from favorites',
            'data' => [
                'is_favorite' => $article->is_favorite
            ]
        ]);
    }

    /**
     * Đánh dấu tất cả bài viết là đã đọc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_id' => 'nullable|exists:feeds,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Article::query()
            ->join('feeds', 'articles.feed_id', '=', 'feeds.id')
            ->where('feeds.user_id', Auth::id())
            ->where('articles.is_read', false);

        // Lọc theo feed nếu được chỉ định
        if ($feedId = $request->input('feed_id')) {
            $query->where('articles.feed_id', $feedId);
        }
        // Lọc theo danh mục nếu được chỉ định
        elseif ($categoryId = $request->input('category_id')) {
            $query->whereHas('feed', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $count = $query->count();

        // Cập nhật trực tiếp qua SQL để tối ưu hiệu suất
        Article::whereIn('id', $query->select('articles.id'))->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => "{$count} articles marked as read"
        ]);
    }
}
