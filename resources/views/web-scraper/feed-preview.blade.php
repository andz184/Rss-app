@extends('layouts.app')

@section('styles')
<style>
    .preview-container {
        max-width: 100%;
        margin-bottom: 30px;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid #f0f0f0;
        padding: 15px 20px;
    }

    .card-body {
        padding: 20px;
    }

    .feed-info {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .feed-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .feed-description {
        color: #666;
        margin-bottom: 10px;
    }

    .feed-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        color: #888;
    }

    .feed-url {
        color: #ff7846;
        word-break: break-all;
        font-size: 0.9rem;
    }

    .feed-items {
        margin-top: 15px;
    }

    .feed-item {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.2s;
    }

    .feed-item:hover {
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }

    .item-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .item-link {
        color: #ff7846;
        font-size: 0.85rem;
        margin-bottom: 10px;
        display: block;
        word-break: break-all;
    }

    .item-description {
        color: #666;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }

    .item-date {
        color: #888;
        font-size: 0.85rem;
    }

    .item-image {
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
        margin: 10px 0;
    }

    .nav-tabs .nav-link {
        color: #495057;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.5rem 1rem;
    }

    .nav-tabs .nav-link.active {
        color: #ff7846;
        background-color: transparent;
        border-bottom: 2px solid #ff7846;
    }

    .xml-content {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 0.9rem;
        white-space: pre-wrap;
        overflow-x: auto;
        max-height: 600px;
        overflow-y: auto;
    }

    .copy-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #495057;
        cursor: pointer;
    }

    .copy-btn:hover {
        background-color: #e9ecef;
    }

    .feed-url-display {
        position: relative;
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        padding-right: 80px;
    }

    .btn-copy {
        background-color: #ff7846;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 15px;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .btn-copy:hover {
        background-color: #e26b3e;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-right: 10px;
    }

    .status-badge-success {
        background-color: #e6f9ee;
        color: #00a650;
    }

    .tab-pane {
        padding: 20px 0;
    }

    /* Highlight XML Tags */
    .xml-content .tag { color: #0a3069; }
    .xml-content .attr { color: #953800; }
    .xml-content .content { color: #24292f; }
    .xml-content .declaration { color: #5a67d8; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                <h1 class="h2">RSS Feed Preview</h1>
                <div>
                    <a href="{{ route('web-scraper.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Feed Generator
                    </a>
                </div>
            </div>

            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle me-2"></i> Your RSS feed has been successfully generated and is ready to use.
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i> Feed URL</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Use this URL to subscribe to your feed in any RSS reader:</p>

                    <div class="feed-url-display">
                        <code id="feed-url">{{ $xmlUrl }}</code>
                        <button class="copy-btn" onclick="copyToClipboard('feed-url')">
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $xmlUrl }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt me-1"></i> Open XML Feed
                        </a>
                        <a href="{{ route('feeds.show', $feed->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-cog me-1"></i> Manage Feed
                        </a>
                    </div>
                </div>
            </div>

            <div class="preview-container">
                <ul class="nav nav-tabs" id="feedTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab" aria-controls="preview" aria-selected="true">
                            <i class="fas fa-eye me-1"></i> Preview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="xml-tab" data-bs-toggle="tab" data-bs-target="#xml" type="button" role="tab" aria-controls="xml" aria-selected="false">
                            <i class="fas fa-code me-1"></i> XML
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="feedTabsContent">
                    <div class="tab-pane fade show active" id="preview" role="tabpanel" aria-labelledby="preview-tab">
                        <div class="feed-info">
                            <div class="feed-title">
                                {{ $feedInfo['title'] ?? $feed->title }}
                            </div>
                            <div class="feed-description">
                                {{ $feedInfo['description'] ?? $feed->description }}
                            </div>
                            <div class="feed-meta">
                                <div>
                                    <span class="status-badge status-badge-success">
                                        <i class="fas fa-check me-1"></i> Active
                                    </span>
                                    <span class="text-muted">
                                        {{ count($feedItems) }} items
                                    </span>
                                </div>
                                <div>
                                    Last updated: {{ \Carbon\Carbon::parse($feedInfo['pubDate'] ?? now())->format('M d, Y H:i') }}
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>Source:</strong>
                                <a href="{{ $feedInfo['link'] ?? $feed->site_url }}" target="_blank" class="feed-url">
                                    {{ $feedInfo['link'] ?? $feed->site_url }}
                                </a>
                            </div>
                        </div>

                        <div class="feed-items">
                            @if(count($feedItems) > 0)
                                @foreach($feedItems as $item)
                                    <div class="feed-item">
                                        <div class="item-title">{{ $item['title'] }}</div>
                                        <a href="{{ $item['link'] }}" target="_blank" class="item-link">
                                            <i class="fas fa-external-link-alt me-1"></i> {{ $item['link'] }}
                                        </a>

                                        @if(!empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="item-image" onerror="this.style.display='none'">
                                        @endif

                                        <div class="item-description">
                                            {!! \Illuminate\Support\Str::limit(strip_tags($item['description']), 300) !!}
                                        </div>

                                        <div class="item-date">
                                            <i class="far fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($item['pubDate'])->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No items found in this feed.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="tab-pane fade" id="xml" role="tabpanel" aria-labelledby="xml-tab">
                        <div class="position-relative">
                            <button class="copy-btn" onclick="copyToClipboard('xml-content')">
                                <i class="fas fa-copy me-1"></i> Copy XML
                            </button>
                            <div id="xml-content" class="xml-content">{{ htmlspecialchars($xmlContent) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            const button = event.currentTarget;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
            setTimeout(function() {
                button.innerHTML = originalText;
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    // Syntax highlighting for XML
    document.addEventListener('DOMContentLoaded', function() {
        const xmlContent = document.getElementById('xml-content');
        if (xmlContent) {
            let xml = xmlContent.textContent;
            xml = xml.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            // Highlight tags, attributes, and content
            xml = xml.replace(/&lt;(\/?[a-zA-Z0-9:_.-]+)(\s+[^&>]*)?&gt;/g, function(match, p1, p2) {
                let attributes = '';
                if (p2) {
                    attributes = p2.replace(/([a-zA-Z0-9:_.-]+)=(&quot;|')(.*?)(&quot;|')/g,
                        ' <span class="attr">$1</span>=<span class="attr">$2$3$4</span>');
                }
                return '<span class="tag">&lt;' + p1 + '</span>' + attributes + '<span class="tag">&gt;</span>';
            });

            // Highlight XML declaration
            xml = xml.replace(/&lt;\?xml[^&]*\?&gt;/g, '<span class="declaration">$&</span>');

            xmlContent.innerHTML = xml;
        }
    });
</script>
@endsection
