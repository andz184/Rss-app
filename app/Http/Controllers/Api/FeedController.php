<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeedController extends Controller
{
    protected FeedService $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->middleware('auth:api');
        $this->feedService = $feedService;
    }

    /**
     * Lấy danh sách tất cả feed của người dùng.
     *
     * @return \Illuminate\Http\JsonResponse
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

        return response()->json([
            'status' => 'success',
            'data' => $feeds
        ]);
    }

    /**
     * Lưu feed mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feed_url' => [
                'required',
                'url',
                Rule::unique('feeds')
                    ->where(fn ($query) => $query->where('user_id', Auth::id()))
            ],
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Set default values for a new feed
        $feed = new Feed([
            'feed_url' => $request->feed_url,
            'title' => 'Loading...',
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
        ]);

        $feed->save();

        // Fetch the feed to get its details
        $result = $this->feedService->fetchAndParse($feed);

        if ($result['status'] !== 'success') {
            $feed->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch feed: ' . ($result['message'] ?? 'Unknown error')
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Feed added successfully',
            'data' => $feed->fresh()
        ], 201);
    }

    /**
     * Lấy thông tin chi tiết một feed.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Feed $feed)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $feed->load('category');
        $feed->loadCount(['articles', 'articles as unread_count' => function($query) {
            $query->where('is_read', false);
        }]);

        $articles = $feed->articles()
            ->orderBy('date', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'feed' => $feed,
                'articles' => $articles
            ]
        ]);
    }

    /**
     * Cập nhật thông tin feed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Feed $feed)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'feed_url' => [
                'sometimes',
                'required',
                'url',
                Rule::unique('feeds')
                    ->where(fn ($query) => $query->where('user_id', Auth::id()))
                    ->ignore($feed->id)
            ],
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $feed->update($request->only(['title', 'feed_url', 'category_id', 'is_active']));

        return response()->json([
            'status' => 'success',
            'message' => 'Feed updated successfully',
            'data' => $feed->fresh()
        ]);
    }

    /**
     * Xóa feed.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Feed $feed)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $feed->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Feed deleted successfully'
        ]);
    }

    /**
     * Làm mới feed để lấy bài viết mới.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Feed $feed)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $result = $this->feedService->fetchAndParse($feed);

        $message = match($result['status']) {
            'success' => "Feed refreshed successfully. {$result['new_articles']} new articles found.",
            'not_modified' => "Feed has not been modified since last check.",
            default => "Error refreshing feed: {$result['message']}",
        };

        $status = $result['status'] === 'error' ? 'error' : 'success';

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $feed->fresh()
        ]);
    }

    /**
     * Đánh dấu tất cả bài viết trong feed là đã đọc.
     *
     * @param  \App\Models\Feed  $feed
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllRead(Feed $feed)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $feed->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $count = $feed->articles()->where('is_read', false)->count();
        $feed->articles()->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => "{$count} articles marked as read"
        ]);
    }
}
