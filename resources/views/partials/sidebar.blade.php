<div class="list-group mb-4">
    <a href="{{ route('articles.index') }}" class="list-group-item list-group-item-action {{ request()->get('filter') === null ? 'active' : '' }}">
        {{ __('All Articles') }}
    </a>
    <a href="{{ route('articles.index', ['filter' => 'unread']) }}" class="list-group-item list-group-item-action {{ request()->get('filter') === 'unread' ? 'active' : '' }}">
        {{ __('Unread') }} <span class="badge bg-primary rounded-pill">{{ $unreadCount ?? 0 }}</span>
    </a>
    <a href="{{ route('articles.index', ['filter' => 'favorites']) }}" class="list-group-item list-group-item-action {{ request()->get('filter') === 'favorites' ? 'active' : '' }}">
        {{ __('Favorites') }} <span class="badge bg-warning rounded-pill">{{ $favoritesCount ?? 0 }}</span>
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">{{ __('Tools') }}</div>
    <div class="list-group list-group-flush">
        <a href="{{ route('web-scraper.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('web-scraper.*') ? 'active' : '' }}">
            <i class="bi bi-code-slash"></i> {{ __('Web to RSS Tool') }}
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('Categories') }}</span>
        <a href="{{ route('categories.create') }}" class="btn btn-sm btn-outline-primary">{{ __('Add') }}</a>
    </div>
    <div class="list-group list-group-flush">
        @forelse ($categories as $category)
            <a href="{{ route('articles.index', ['category' => $category->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->get('category') == $category->id ? 'active' : '' }}">
                <span>
                    @if ($category->color)
                        <span class="badge" style="background-color: {{ $category->color }};">&nbsp;</span>
                    @endif
                    {{ $category->name }}
                </span>
                <span class="badge bg-secondary rounded-pill">{{ $category->feeds_count }}</span>
            </a>
        @empty
            <div class="list-group-item">
                <p class="mb-0">{{ __('No categories found.') }}</p>
            </div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('Feeds') }}</span>
        <a href="{{ route('feeds.create') }}" class="btn btn-sm btn-outline-primary">{{ __('Add') }}</a>
    </div>
    <div class="list-group list-group-flush">
        @forelse ($feeds as $feed)
            <a href="{{ route('articles.index', ['feed' => $feed->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request()->get('feed') == $feed->id ? 'active' : '' }}">
                <span class="d-flex align-items-center">
                    @if ($feed->icon)
                        <img src="{{ $feed->icon }}" alt="{{ $feed->title }}" class="me-2" style="width: 16px; height: 16px;">
                    @endif
                    {{ $feed->title }}
                </span>
                @if ($feed->unread_count)
                    <span class="badge bg-primary rounded-pill">{{ $feed->unread_count }}</span>
                @endif
            </a>
        @empty
            <div class="list-group-item">
                <p class="mb-0">{{ __('No feeds found.') }}</p>
            </div>
        @endforelse
    </div>
</div>
