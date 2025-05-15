<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = Auth::user()->categories()->orderBy('order')->get();

        // Get feeds for sidebar
        $feeds = Auth::user()->feeds()
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Auth::user()->articles()->where('is_read', false)->count();
        $favoritesCount = Auth::user()->articles()->where('is_favorite', true)->count();

        return view('categories.index', compact('categories', 'feeds', 'unreadCount', 'favoritesCount'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $categories = Auth::user()->categories()->orderBy('name')->get();

        // Get feeds for sidebar
        $feeds = Auth::user()->feeds()
            ->with('category')
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Auth::user()->articles()->where('is_read', false)->count();
        $favoritesCount = Auth::user()->articles()->where('is_favorite', true)->count();

        return view('categories.create', compact('categories', 'feeds', 'unreadCount', 'favoritesCount'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('categories')->where(fn ($query) => $query->where('user_id', Auth::id()))
            ],
            'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $validated['user_id'] = Auth::id();

        // Set the order to be the last in the list
        $maxOrder = Auth::user()->categories()->max('order') ?? 0;
        $validated['order'] = $maxOrder + 1;

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        $categories = Auth::user()->categories()
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        // Get feeds for sidebar
        $feeds = Auth::user()->feeds()
            ->with('category')
            ->withCount(['articles', 'articles as unread_count' => function($query) {
                $query->where('is_read', false);
            }])
            ->orderBy('title')
            ->get();

        // Get unread and favorites count for sidebar
        $unreadCount = Auth::user()->articles()->where('is_read', false)->count();
        $favoritesCount = Auth::user()->articles()->where('is_favorite', true)->count();

        return view('categories.edit', compact('category', 'categories', 'feeds', 'unreadCount', 'favoritesCount'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('categories')
                    ->where(fn ($query) => $query->where('user_id', Auth::id()))
                    ->ignore($category->id)
            ],
            'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    // Prevent circular references
                    if ($value == $category->id) {
                        $fail('Category cannot be its own parent.');
                    }
                }
            ],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        // Move feeds to uncategorized
        $category->feeds()->update(['category_id' => null]);

        // Delete the category
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Update the order of categories.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        $order = $request->input('order');
        $categories = Auth::user()->categories()->whereIn('id', $order)->get()->keyBy('id');

        foreach ($order as $position => $categoryId) {
            if (isset($categories[$categoryId])) {
                $categories[$categoryId]->update(['order' => $position]);
            }
        }

        return response()->json(['success' => true]);
    }
}
