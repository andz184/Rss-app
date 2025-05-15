@extends('layouts.app')

@section('styles')
<style>
    .section-title {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 15px;
    }

    .section-subtitle {
        color: #6c757d;
        font-size: 1rem;
        margin-bottom: 30px;
    }

    .steps-container {
        display: flex;
        margin: 30px 0;
    }

    .step-item {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step-item:not(:last-child):after {
        content: '';
        position: absolute;
        top: 15px;
        right: 0;
        width: calc(100% - 30px);
        height: 2px;
        background-color: #e9ecef;
        z-index: 1;
    }

    .step-circle {
        width: 30px;
        height: 30px;
        background-color: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        position: relative;
        z-index: 2;
    }

    .step-item.active .step-circle {
        background-color: #ff7846;
        color: white;
    }

    .step-item.complete .step-circle {
        background-color: #28a745;
        color: white;
    }

    .step-label {
        font-weight: 500;
        margin-bottom: 5px;
    }

    .step-desc {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid #f0f0f0;
        border-radius: 10px 10px 0 0 !important;
        padding: 20px 25px;
    }

    .card-body {
        padding: 25px;
    }

    .btn-primary {
        background-color: #ff7846;
        border-color: #ff7846;
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 6px;
    }

    .btn-primary:hover {
        background-color: #e26b3e;
        border-color: #e26b3e;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border-radius: 6px;
        padding: 10px 15px;
        border: 1px solid #ced4da;
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(255, 120, 70, 0.25);
        border-color: #ff7846;
    }

    .form-check-input:checked {
        background-color: #ff7846;
        border-color: #ff7846;
    }

    .preview-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .preview-title {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .preview-url {
        font-size: 0.8rem;
        color: #ff7846;
        margin-bottom: 10px;
        word-break: break-all;
    }

    .preview-description {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .preview-date {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 5px;
    }

    .feed-info-card {
        background-color: rgba(255, 120, 70, 0.05);
        border: 1px solid rgba(255, 120, 70, 0.1);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .feed-url {
        background-color: white;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 10px;
        font-family: monospace;
        margin-top: 10px;
        word-break: break-all;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: #ff7846;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }

    .toggle-label {
        margin-left: 10px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="section-title">Configure Your RSS Feed</h1>
            <p class="section-subtitle">Customize and preview your feed before generating it</p>

            <!-- Steps Navigation -->
            <div class="steps-container">
                <div class="step-item complete">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-label">Enter webpage URL</div>
                    <div class="step-desc">and click 'Load Website'</div>
                </div>
                <div class="step-item complete">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-label">Use visual select</div>
                    <div class="step-desc">to click on webpage post elements</div>
                </div>
                <div class="step-item active">
                    <div class="step-circle">3</div>
                    <div class="step-label">Preview the feed</div>
                    <div class="step-desc">and click 'Generate'</div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Feed Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('web-scraper.generate') }}" method="POST" id="configure-form">
                                @csrf
                                <input type="hidden" name="url" value="{{ $url ?? '' }}">
                                <input type="hidden" name="css_selector" value="{{ $css_selector ?? '' }}">
                                <input type="hidden" name="title_selector" value="{{ $title_selector ?? '' }}">

                                <div class="mb-3">
                                    <label for="feed_title" class="form-label">Feed Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="feed_title" name="feed_title" value="{{ $feed_title ?? 'Feed from ' . parse_url($url ?? '', PHP_URL_HOST) }}" required>
                                    <div class="form-text">Give your feed a descriptive name</div>
                                </div>

                                <div class="mb-3">
                                    <label for="feed_description" class="form-label">Feed Description</label>
                                    <textarea class="form-control" id="feed_description" name="feed_description" rows="3">{{ $feed_description ?? 'RSS feed created from ' . ($url ?? '') }}</textarea>
                                    <div class="form-text">A brief description of what's in this feed</div>
                                </div>

                                <div class="mb-3">
                                    <label for="update_frequency" class="form-label">Update Frequency</label>
                                    <select class="form-select" id="update_frequency" name="update_frequency">
                                        <option value="30">Every 30 minutes</option>
                                        <option value="60" selected>Every hour</option>
                                        <option value="180">Every 3 hours</option>
                                        <option value="360">Every 6 hours</option>
                                        <option value="720">Every 12 hours</option>
                                        <option value="1440">Daily</option>
                                    </select>
                                    <div class="form-text">How often should we check for new content</div>
                                </div>

                                <div class="mb-3">
                                    <label for="items_limit" class="form-label">Items Limit</label>
                                    <select class="form-select" id="items_limit" name="items_limit">
                                        <option value="10">10 items</option>
                                        <option value="20" selected>20 items</option>
                                        <option value="50">50 items</option>
                                        <option value="100">100 items</option>
                                    </select>
                                    <div class="form-text">Maximum number of items to include in your feed</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Advanced Options</label>
                                    <div class="d-flex mb-3 align-items-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="full_content" name="full_content" value="1" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-label">Extract full content</span>
                                    </div>

                                    <div class="d-flex mb-3 align-items-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="include_images" name="include_images" value="1" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-label">Include images</span>
                                    </div>

                                    <div class="d-flex mb-3 align-items-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="include_dates" name="include_dates" value="1" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-label">Extract publication dates</span>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('web-scraper.rss-creator', ['url' => $url ?? '']) }}" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-arrow-left me-1"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-rss me-1"></i> Generate RSS Feed
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Feed Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="feed-info-card mb-4">
                                <p class="mb-2"><strong>Source URL:</strong></p>
                                <div class="feed-url">{{ $url ?? 'https://example.com' }}</div>

                                <p class="mb-2 mt-3"><strong>CSS Selector:</strong></p>
                                <div class="feed-url">{{ $css_selector ?? 'article' }}</div>

                                <p class="mb-0 mt-3"><strong>Elements Selected:</strong> {{ $element_count ?? '5' }}</p>
                            </div>

                            <div class="preview-items">
                                <h6 class="mb-3">Preview of Items:</h6>

                                @if(!empty($preview_items))
                                    @foreach($preview_items as $item)
                                        <div class="preview-card">
                                            <div class="preview-title">{{ $item['title'] ?? 'Example Item Title' }}</div>
                                            <div class="preview-url">{{ $item['url'] ?? 'https://example.com/article1' }}</div>
                                            <div class="preview-description">{{ $item['description'] ?? 'This is an example description of the article content that will appear in your RSS feed.' }}</div>
                                            <div class="preview-date">{{ $item['date'] ?? date('Y-m-d') }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Example previews when actual data is not available -->
                                    <div class="preview-card">
                                        <div class="preview-title">Example Article Title</div>
                                        <div class="preview-url">https://example.com/article1</div>
                                        <div class="preview-description">This is an example description of the article content that will appear in your RSS feed.</div>
                                        <div class="preview-date">{{ date('Y-m-d') }}</div>
                                    </div>

                                    <div class="preview-card">
                                        <div class="preview-title">Another Example Article</div>
                                        <div class="preview-url">https://example.com/article2</div>
                                        <div class="preview-description">Your feed will include snippets of content like this from each article that matches your selected elements.</div>
                                        <div class="preview-date">{{ date('Y-m-d', strtotime('-1 day')) }}</div>
                                    </div>
                                @endif

                                <div class="text-center mt-3">
                                    <span class="text-muted">+ more items will be included in the full feed</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Feed Usage</h5>
                        </div>
                        <div class="card-body">
                            <p>After generating your feed, you'll receive a unique URL that you can use in any RSS reader or application.</p>

                            <p>Common uses:</p>
                            <ul>
                                <li>Subscribe in your favorite RSS reader</li>
                                <li>Create automated workflows with tools like IFTTT or Zapier</li>
                                <li>Monitor content updates without visiting the website</li>
                                <li>Integrate content into your own application</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
