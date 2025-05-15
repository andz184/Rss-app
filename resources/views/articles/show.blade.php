@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar')
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h1 class="h4 mb-0">{{ $article->title }}</h1>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <span class="badge bg-secondary">{{ $article->feed->title }}</span>
                        @if ($article->date)
                            <span class="text-muted">{{ $article->date->format('F j, Y g:i A') }}</span>
                        @endif
                        @if ($article->author)
                            <span class="text-muted">• {{ $article->author }}</span>
                        @endif
                    </div>
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
                        <a href="{{ $article->url }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('Visit Site') }}</a>
                    </div>
                </div>

                <div class="article-content mb-4">
                    {!! $article->content !!}
                </div>

                <div class="d-flex justify-content-between">
                    <div>
                        @if ($prevArticle)
                            <a href="{{ route('articles.show', $prevArticle) }}" class="btn btn-outline-secondary">
                                &laquo; {{ __('Previous') }}
                            </a>
                        @endif
                    </div>
                    <div>
                        @if ($nextArticle)
                            <a href="{{ route('articles.show', $nextArticle) }}" class="btn btn-outline-secondary">
                                {{ __('Next') }} &raquo;
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .article-content {
        max-width: 100%;
        overflow-x: auto;
    }
    .article-content img {
        max-width: 100%;
        height: auto;
    }
</style>
@endpush
