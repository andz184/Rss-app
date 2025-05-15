@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @php
            $unreadCount = App\Models\Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
                ->where('feeds.user_id', auth()->id())
                ->where('is_read', false)
                ->count();

            $favoritesCount = App\Models\Article::join('feeds', 'articles.feed_id', '=', 'feeds.id')
                ->where('feeds.user_id', auth()->id())
                ->where('is_favorite', true)
                ->count();
        @endphp
        @include('partials.sidebar', ['unreadCount' => $unreadCount, 'favoritesCount' => $favoritesCount])
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    @if(request()->get('filter') === 'unread')
                        {{ __('Unread Articles') }}
                    @elseif(request()->get('filter') === 'favorites')
                        {{ __('Favorite Articles') }}
                    @elseif(request()->get('category'))
                        {{ $categories->firstWhere('id', request()->get('category'))?->name ?? __('Category Articles') }}
                    @elseif(request()->get('feed'))
                        {{ $feeds->firstWhere('id', request()->get('feed'))?->title ?? __('Feed Articles') }}
                    @else
                        {{ __('All Articles') }}
                    @endif
                </h5>
                <div>
                    <form action="{{ route('articles.mark-all-read') }}" method="POST" class="d-inline">
                        @csrf
                        @if(request()->get('feed'))
                            <input type="hidden" name="feed_id" value="{{ request()->get('feed') }}">
                        @elseif(request()->get('category'))
                            <input type="hidden" name="category_id" value="{{ request()->get('category') }}">
                        @endif
                        <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('{{ __('Are you sure you want to mark all articles as read?') }}')">
                            {{ __('Mark All Read') }}
                        </button>
                    </form>
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ __('Sort') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}">{{ __('Newest First') }}</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}">{{ __('Oldest First') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="list-group list-group-flush article-list">
                @forelse($articles as $article)
                    <a href="{{ route('articles.show', $article) }}"
                       class="list-group-item list-group-item-action article-item {{ $article->is_read ? 'article-read' : 'article-unread' }}">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                            <h5 class="mb-1 article-title">{{ $article->title }}</h5>
                            <div class="d-flex align-items-center">
                                @if($article->is_favorite)
                                    <span class="badge bg-warning me-2"><i class="fas fa-star"></i></span>
                                @endif
                                <small class="text-muted">{{ $article->date->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <small class="text-muted">
                                <span class="badge" style="background-color: {{ $article->feed->category->color ?? '#6c757d' }};">&nbsp;</span>
                                {{ $article->feed->title }}
                                @if($article->author)
                                    • {{ $article->author }}
                                @endif
                            </small>
                            <div class="btn-group btn-group-sm article-actions">
                                <form action="{{ route('articles.toggle-read', $article) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link p-0 me-3 text-muted">
                                        <i class="fas {{ $article->is_read ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('articles.toggle-favorite', $article) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link p-0 text-muted">
                                        <i class="fas fa-star {{ $article->is_favorite ? 'text-warning' : '' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item">
                        <p class="mb-0 text-center py-4">{{ __('No articles found.') }}</p>
                        <div class="text-center">
                            <a href="{{ route('feeds.create') }}" class="btn btn-primary">{{ __('Add New Feed') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>
            <div class="card-footer">
                {{ $articles->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .article-item {
        transition: all 0.2s ease;
    }
    .article-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    .article-read .article-title {
        font-weight: normal;
        color: var(--text-muted);
    }
    .article-unread .article-title {
        font-weight: bold;
    }
    .article-actions {
        display: none;
    }
    .article-item:hover .article-actions {
        display: flex;
    }
</style>
@endpush
