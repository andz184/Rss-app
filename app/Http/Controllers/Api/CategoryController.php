<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Lấy danh sách tất cả danh mục của người dùng.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Auth::user()->categories()
            ->withCount('feeds')
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Lưu danh mục mới.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $lastOrder = Auth::user()->categories()->max('order') ?? 0;

        $category = new Category([
            'name' => $request->name,
            'color' => $request->color ?? '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
            'order' => $request->order ?? $lastOrder + 1,
            'parent_id' => $request->parent_id,
            'user_id' => Auth::id()
        ]);

        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Lấy thông tin chi tiết một danh mục.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $category->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $category->load(['feeds' => function($query) {
            $query->withCount(['articles', 'articles as unread_count' => function($q) {
                $q->where('is_read', false);
            }]);
        }]);

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    /**
     * Cập nhật thông tin danh mục.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $category->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only(['name', 'color', 'order', 'parent_id']));

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Xóa danh mục.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        // Kiểm tra quyền truy cập
        if (Auth::id() !== $category->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Kiểm tra xem có feed nào thuộc danh mục này không
        $feedCount = $category->feeds()->count();

        if ($feedCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot delete category that contains {$feedCount} feeds. Move the feeds to another category first."
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Cập nhật thứ tự của các danh mục.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->categories as $item) {
            $category = Category::find($item['id']);

            // Kiểm tra quyền truy cập
            if (Auth::id() !== $category->user_id) {
                continue;
            }

            $category->update(['order' => $item['order']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories order updated successfully'
        ]);
    }
}
