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
                <h5 class="mb-0">{{ __('Manage Feeds') }}</h5>
                <a href="{{ route('feeds.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i>{{ __('Add New Feed') }}
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Articles') }}</th>
                            <th>{{ __('Last Updated') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeds as $feed)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($feed->icon)
                                            <img src="{{ $feed->icon }}" alt="{{ $feed->title }}" class="me-2" style="width: 16px; height: 16px;">
                                        @else
                                            <i class="fas fa-rss me-2 text-muted"></i>
                                        @endif
                                        <a href="{{ route('articles.index', ['feed' => $feed->id]) }}">{{ $feed->title }}</a>
                                    </div>
                                </td>
                                <td>
                                    @if($feed->category)
                                        <span class="badge" style="background-color: {{ $feed->category->color }};">&nbsp;</span>
                                        {{ $feed->category->name }}
                                    @else
                                        <span class="text-muted">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $feed->articles_count }}
                                    @if($feed->unread_count > 0)
                                        <span class="badge bg-primary">{{ $feed->unread_count }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($feed->last_updated)
                                        <span title="{{ $feed->last_updated }}">{{ $feed->last_updated->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted">{{ __('Never') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($feed->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <form action="{{ route('feeds.refresh', $feed) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Refresh Feed') }}">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('feeds.edit', $feed) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Edit Feed') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('feeds.destroy', $feed) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this feed? All articles will be deleted too.') }}')"
                                                    title="{{ __('Delete Feed') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="mb-3">{{ __('No feeds found. Add your first feed to start reading!') }}</p>
                                    <a href="{{ route('feeds.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i>{{ __('Add New Feed') }}
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
