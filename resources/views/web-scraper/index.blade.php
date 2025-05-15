@extends('layouts.app')

@section('styles')
<style>
    /* Modern UI Styles with blue/orange color scheme */
    body {
        background-color: #f8f9fb;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    .input-group {
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }

    .input-group-text {
        background-color: white;
        border-right: 0;
    }

    .form-control {
        border-left: 0;
        padding: 12px 15px;
        font-size: 1rem;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }

    .step-container {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }

    .step-number {
        position: absolute;
        left: 0;
        top: 0;
        width: 30px;
        height: 30px;
        background-color: #ff7846;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .step-text {
        font-weight: 500;
        color: #333;
        margin-bottom: 5px;
    }

    .step-description {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .section-title {
        font-size: 32px;
        font-weight: 700;
        color: #333;
        margin-bottom: 15px;
    }

    .section-subtitle {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 30px;
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        background-color: rgba(255, 120, 70, 0.1);
        color: #ff7846;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .feature-title {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .feature-text {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .table {
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .table tbody tr {
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        border-radius: 8px;
    }

    .table td, .table th {
        padding: 15px;
        vertical-align: middle;
        border: none;
    }

    .table tbody tr td:first-child {
        border-radius: 8px 0 0 8px;
    }

    .table tbody tr td:last-child {
        border-radius: 0 8px 8px 0;
    }

    /* Loading Overlay */
    #loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loading-container {
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        text-align: center;
        max-width: 400px;
    }

    .loading-spinner {
        width: 4rem;
        height: 4rem;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #ff7846;
        border-radius: 50%;
        margin: 0 auto 20px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Preview Images */
    .preview-img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    /* Steps UI */
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

    /* Improved Feed Table */
    .text-orange {
        color: #ff7846;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Process Steps - Larger and more prominent */
    .process-steps {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin: 30px 0;
    }

    .process-step {
        display: flex;
        align-items: flex-start;
    }

    .process-step-number {
        width: 40px;
        height: 40px;
        min-width: 40px;
        background-color: #ff7846;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 15px;
    }

    .process-step-content {
        flex: 1;
    }

    .process-step-title {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .process-step-desc {
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<!-- Loading Overlay -->
<div id="loading-overlay">
    <div class="loading-container">
        <div class="loading-spinner"></div>
        <h4>Loading website...</h4>
        <p>This may take a few moments</p>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        @include('partials.sidebar')

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="section-title">Create RSS Feed from Any Website</h1>
            <p class="section-subtitle">Generate RSS feeds from web pages that don't have built-in feeds in just three simple steps</p>

            <!-- Steps Navigation -->
            <div class="steps-container">
                <div class="step-item active">
                    <div class="step-circle">1</div>
                    <div class="step-label">Enter webpage URL</div>
                    <div class="step-desc">and click 'Load Website'</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">2</div>
                    <div class="step-label">Use visual select</div>
                    <div class="step-desc">to click on webpage post elements</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">3</div>
                    <div class="step-label">Preview the feed</div>
                    <div class="step-desc">and click 'Generate'</div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4"><i class="fas fa-link text-primary me-2"></i> Enter Website URL</h5>

                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form action="{{ route('web-scraper.popup') }}" method="GET" id="url-form">
                                <div class="mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                        <input type="url" class="form-control" id="url" name="url"
                                            placeholder="https://example.com" required
                                            value="{{ old('url') }}">
                                        <button type="submit" id="submit-button" class="btn btn-primary">
                                            Load Website
                                        </button>
                                    </div>
                                    <div class="form-text mt-2">Enter the full URL including https:// or http://</div>
                                </div>

                                <div class="d-flex align-items-center mt-4">
                                    <div class="me-3">
                                        <i class="fas fa-lock text-secondary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">We respect website policies and only create feeds for sites that allow it</small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="fas fa-info-circle text-info me-2"></i> Quick Help</h5>

                            <div class="mb-3">
                                <p class="text-muted">Not sure how to get started? Here's what to do:</p>
                            </div>

                            <div class="process-steps">
                                <div class="process-step">
                                    <div class="process-step-number">1</div>
                                    <div class="process-step-content">
                                        <div class="process-step-title">Enter webpage URL and click 'Load Website'</div>
                                        <div class="process-step-desc">Enter the full website URL in the input field on the left</div>
                                    </div>
                                </div>

                                <div class="process-step">
                                    <div class="process-step-number">2</div>
                                    <div class="process-step-content">
                                        <div class="process-step-title">Use visual select to click on webpage post elements</div>
                                        <div class="process-step-desc">In the next step, you'll be able to click on content elements</div>
                                    </div>
                                </div>

                                <div class="process-step">
                                    <div class="process-step-number">3</div>
                                    <div class="process-step-content">
                                        <div class="process-step-title">Preview the feed and click 'Generate'</div>
                                        <div class="process-step-desc">Configure your feed options and create your RSS feed</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('help.rss-guide') }}" class="text-decoration-none">
                                    <i class="fas fa-external-link-alt me-1"></i> View detailed guide
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Website Preview Image -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="mb-4">How It Works</h5>
                            <img src="{{ asset('images/rss-creator-preview.png') }}" alt="RSS Creator Preview" class="img-fluid preview-img" onerror="this.style.display='none'">

                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-mouse-pointer"></i>
                                    </div>
                                    <h6 class="feature-title">Visual Selection</h6>
                                    <p class="feature-text">Click directly on elements in the webpage to select content for your feed</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-magic"></i>
                                    </div>
                                    <h6 class="feature-title">Automatic Detection</h6>
                                    <p class="feature-text">Our system automatically detects titles, links, dates, and descriptions</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-icon">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <h6 class="feature-title">Customizable</h6>
                                    <p class="feature-text">Configure update frequency, item limits, and other feed settings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Your RSS Feeds -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-rss me-2 text-orange"></i> Your RSS Feeds</h5>
                        </div>
                        <div class="card-body">
                            @if(count($feeds->where('is_scraped', true)) > 0)
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Feed Name</th>
                                                <th>Source</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($feeds->where('is_scraped', true) as $feed)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-rss text-orange me-3"></i>
                                                        <span>{{ $feed->title }}</span>
                                                    </div>
                                                </td>
                                                <td><a href="{{ $feed->site_url }}" target="_blank" class="text-decoration-none">{{ \Str::limit($feed->site_url, 40) }}</a></td>
                                                <td>{{ $feed->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <a href="{{ route('feeds.show', $feed) }}" class="btn btn-sm btn-light me-1" title="View Feed">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('rss.preview', $feed->id) }}" class="btn btn-sm btn-primary me-1" title="Preview RSS Feed">
                                                        <i class="fas fa-file-code"></i>
                                                    </a>
                                                    <a href="{{ route('rss.show', $feed->id) }}" class="btn btn-sm btn-success" target="_blank" title="Open RSS Feed">
                                                        <i class="fas fa-rss"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div style="font-size: 3rem; color: #e9ecef; margin-bottom: 1rem;">
                                        <i class="fas fa-rss"></i>
                                    </div>
                                    <h5>You haven't created any web-scraped RSS feeds yet</h5>
                                    <p class="text-muted">Enter a website URL above to get started</p>
                                </div>
                            @endif
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
    // Handle form submission
    document.getElementById('url-form').addEventListener('submit', function(e) {
        const urlInput = document.getElementById('url');
        const submitButton = document.getElementById('submit-button');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Basic URL validation
        if (!urlInput.value.match(/^https?:\/\/.+\..+/)) {
            e.preventDefault();
            alert('Please enter a valid URL starting with http:// or https://');
            return;
        }

        // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
        loadingOverlay.style.display = 'flex';

        // Set a timeout to hide loading if it takes too long (30 seconds)
            setTimeout(function() {
                if (loadingOverlay.style.display === 'flex') {
                    loadingOverlay.style.display = 'none';
                    submitButton.disabled = false;
                submitButton.innerHTML = 'Load Website';
                    alert('Loading is taking longer than expected. Please try again or try a different URL.');
                }
            }, 30000);
    });
</script>
@endsection

