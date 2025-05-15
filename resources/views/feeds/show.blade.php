@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar')
    </div>
    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    @if ($feed->icon)
                        <img src="{{ $feed->icon }}" alt="{{ $feed->title }}" class="me-2" style="width: 24px; height: 24px;">
                    @endif
                    <h1 class="h5 mb-0">{{ $feed->title }}</h1>
                </div>
                <div>
                    <form action="{{ route('feeds.refresh', $feed) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Refresh Feed') }}</button>
                    </form>
                    <a href="{{ route('feeds.edit', $feed) }}" class="btn btn-sm btn-secondary">{{ __('Edit') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        @if ($feed->description)
                            <p>{{ $feed->description }}</p>
                        @endif
                        <p>
                            <a href="{{ $feed->site_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                {{ __('Visit Website') }}
                            </a>
                            <a href="{{ $feed->feed_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                {{ __('View Raw Feed') }}
                            </a>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-1">
                                    <strong>{{ __('Category') }}:</strong>
                                    @if ($feed->category)
                                        <span class="badge" style="background-color: {{ $feed->category->color ?? '#6c757d' }};">
                                            {{ $feed->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('Uncategorized') }}</span>
                                    @endif
                                </p>
                                <p class="mb-1">
                                    <strong>{{ __('Last Updated') }}:</strong>
                                    @if ($feed->last_updated)
                                        {{ $feed->last_updated->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">{{ __('Never') }}</span>
                                    @endif
                                </p>
                                <p class="mb-1">
                                    <strong>{{ __('Status') }}:</strong>
                                    @if ($feed->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </p>
                                @if ($feed->language)
                                    <p class="mb-1">
                                        <strong>{{ __('Language') }}:</strong>
                                        {{ $feed->language }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('Articles') }}</span>
                <form action="{{ route('feeds.mark-all-read', $feed) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Mark All Read') }}</button>
                </form>
            </div>

            <div class="list-group list-group-flush">
                @forelse ($articles as $article)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <a href="{{ route('articles.show', $article) }}" class="article-title {{ $article->is_read ? '' : 'fw-bold' }}">
                                {{ $article->title }}
                            </a>
                            <div>
                                @if ($article->is_favorite)
                                    <span class="badge bg-warning">★</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-muted small mb-2">
                            {{ $article->date->diffForHumans() }}
                            @if ($article->author)
                                • {{ $article->author }}
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div>
                                <form action="{{ route('articles.toggle-read', $article) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $article->is_read ? 'btn-outline-secondary' : 'btn-primary' }}">
                                        {{ $article->is_read ? __('Mark Unread') : __('Mark Read') }}
                                    </button>
                                </form>
                                <form action="{{ route('articles.toggle-favorite', $article) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $article->is_favorite ? 'btn-warning' : 'btn-outline-warning' }}">
                                        {{ $article->is_favorite ? __('Unfavorite') : __('Favorite') }}
                                    </button>
                                </form>
                            </div>
                            <a href="{{ $article->url }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('Visit Site') }}</a>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item">
                        <p class="mb-0">{{ __('No articles found.') }}</p>
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
