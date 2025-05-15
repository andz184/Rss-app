@extends('layouts.app')

@section('styles')
<style>
    .selector-box {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }

    #css-selector {
        font-family: monospace;
    }

    .example-card {
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
        background-color: #f9f9f9;
        cursor: pointer;
        transition: all 0.2s;
    }

    .example-card:hover {
        border-color: #4a6cf7;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .example-card h6 {
        margin-bottom: 5px;
        color: #333;
    }

    .example-card code {
        display: block;
        padding: 5px;
        background: #f0f0f0;
        border-radius: 3px;
        color: #d63384;
    }

    .website-container {
        display: flex;
        flex-direction: column;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .website-toolbar {
        background-color: #f1f3f5;
        padding: 10px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
    }

    .toolbar-back {
        margin-right: 10px;
        color: #6c757d;
    }

    .toolbar-url {
        flex-grow: 1;
        padding: 6px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: white;
    }

    .website-preview {
        height: 500px;
        width: 100%;
        border: none;
        position: relative;
    }

    .website-preview-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    #preview-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .matching-entries {
        max-height: 400px;
        overflow-y: auto;
        margin-top: 15px;
    }

    .matching-entry {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .matching-entry:hover {
        background-color: #f8f9fa;
    }

    .matching-entry-title {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .matching-entry-url {
        font-size: 0.8rem;
        color: #4a6cf7;
        margin-top: 5px;
        word-break: break-all;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        cursor: pointer;
    }

    .content-type-selector {
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .selected-item {
        background-color: rgba(74, 108, 247, 0.1);
        border-left: 3px solid #4a6cf7;
    }

    .form-section {
        background-color: white;
        padding: 15px;
        border-radius: 5px;
        margin-top: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .two-column-layout {
        display: flex;
        gap: 20px;
    }

    .left-column {
        flex: 3;
    }

    .right-column {
        flex: 2;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 992px) {
        .two-column-layout {
            flex-direction: column;
        }
    }

    .highlighted {
        outline: 2px solid red !important;
        background-color: rgba(255, 0, 0, 0.1) !important;
    }

    /* Full-page loading overlay */
    #full-page-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: opacity 0.3s ease-out;
    }

    #full-page-loading .spinner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    #full-page-loading .spinner-border {
        width: 4rem;
        height: 4rem;
        margin-bottom: 1rem;
    }

    #full-page-loading h4 {
        margin-bottom: 0.5rem;
        color: #333;
    }

    #full-page-loading p {
        color: #6c757d;
        text-align: center;
    }

    /* Styling for matching entries to match screenshots */
    .matching-entries {
        border: 1px solid #e9ecef;
        border-radius: 5px;
        overflow: hidden;
    }

    .matching-entry {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.2s;
    }

    .matching-entry:hover {
        background-color: #f8f9fa;
    }

    .matching-entry-title {
        color: #212529;
        font-size: 1rem;
        margin-bottom: 8px;
    }

    .matching-entry-description {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 8px;
    }

    .matching-entry-url {
        color: #fd7e14;
        font-size: 0.8rem;
    }

    /* Orange highlight for selected elements */
    .similar-highlight {
        outline: 2px dashed #fd7e14 !important;
        background-color: rgba(253, 126, 20, 0.05) !important;
    }

    .clicked-highlight {
        outline: 2px solid #fd7e14 !important;
        background-color: rgba(253, 126, 20, 0.1) !important;
    }
</style>
@endsection

@section('content')
<!-- Full-page loading overlay -->
<div id="full-page-loading">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4>Đang tải trang...</h4>
        <p>Vui lòng đợi trong khi hệ thống tải nội dung.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        @include('partials.sidebar')
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">{{ __('Create RSS Feed') }}</div>

            <div class="card-body">
                <h5 class="mb-3">{{ __('Website') }}: <a href="{{ $url }}" target="_blank">{{ $url }}</a></h5>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Hãy nhập CSS selector để chọn các phần tử chứa bài viết trên trang web của bạn. Bạn có thể sử dụng công cụ kiểm tra phần tử của trình duyệt (F12) để tìm CSS selector phù hợp.
                </div>

                <div class="two-column-layout">
                    <div class="left-column">
                        <div class="website-container">
                            <div class="website-toolbar">
                                <div class="toolbar-back">
                                    <i class="fas fa-arrow-left"></i>
                                </div>
                                <div class="toolbar-url">{{ $url }}</div>
                                <div class="ms-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="render-js-checkbox">
                                        <label class="form-check-label" for="render-js-checkbox">
                                            Render JavaScript
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="website-preview">
                                <div class="website-preview-loading" id="preview-loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <iframe id="preview-iframe" src="{{ route('web-scraper.proxy', ['url' => $url]) }}" sandbox="allow-same-origin allow-scripts"></iframe>
                            </div>
                        </div>
                    </div>

                    <div class="right-column">
                        <div class="selector-box">
                            <h6>{{ __('CSS Selector') }}:</h6>
                            <div class="input-group mb-3 input-with-icon">
                                <input type="text" id="css-selector" class="form-control" placeholder="Ví dụ: article, .news-item, #content div.post" value="article">
                                <i class="fas fa-magic" id="detect-selector" title="Tự động phát hiện selector"></i>
                            </div>
                            <div class="form-text text-muted">
                                {{ __('Nhập CSS selector để chọn các phần tử chứa bài viết.') }}
                            </div>

                            <div class="mt-3">
                                <h6>{{ __('Selector Examples') }}:</h6>
                                <div class="example-card mb-2" onclick="setSelector('article')">
                                    <h6>Bài viết</h6>
                                    <code>article</code>
                                    <small>Chọn tất cả các thẻ article</small>
                                </div>
                                <div class="example-card mb-2" onclick="setSelector('.news-item')">
                                    <h6>Class news-item</h6>
                                    <code>.news-item</code>
                                    <small>Chọn tất cả phần tử có class="news-item"</small>
                                </div>
                                <div class="example-card" onclick="setSelector('#content .post')">
                                    <h6>Posts trong content</h6>
                                    <code>#content .post</code>
                                    <small>Chọn các post trong phần content</small>
                                </div>
                            </div>
                        </div>

                        <div class="selector-box">
                            <h6>{{ __('Matching entries') }} <i class="fas fa-chevron-down"></i></h6>
                            <div class="matching-entries" id="matching-entries">
                                <div class="text-center py-3 text-muted">
                                    <i class="fas fa-spin fa-spinner me-2"></i> Loading matching entries...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('web-scraper.generate') }}" class="form-section" id="generate-form">
                    @csrf
                    <input type="hidden" name="css_selector" id="selected-css-path" value="article">
                    <input type="hidden" name="title_selector" id="selected-title-path" value="">

                    <div class="row mb-3">
                        <label for="feed_title" class="col-md-3 col-form-label">{{ __('Feed Title') }}</label>
                        <div class="col-md-9">
                            <input id="feed_title" type="text" class="form-control @error('feed_title') is-invalid @enderror" name="feed_title" required>
                            @error('feed_title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="content_type" class="col-md-3 col-form-label">{{ __('Content Type') }}</label>
                        <div class="col-md-9">
                            <select id="content_type" class="form-select @error('content_type') is-invalid @enderror" name="content_type" required>
                                <option value="news">{{ __('News') }}</option>
                                <option value="blog">{{ __('Blog') }}</option>
                                <option value="videos">{{ __('Videos') }}</option>
                                <option value="products">{{ __('Products') }}</option>
                            </select>
                            @error('content_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-primary" id="generate-btn">
                                {{ __('Generate RSS Feed') }}
                            </button>
                            <a href="{{ route('web-scraper.index') }}" class="btn btn-secondary">
                                {{ __('Start Over') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cssSelector = document.getElementById('css-selector');
    const selectedCssPath = document.getElementById('selected-css-path');
    const previewIframe = document.getElementById('preview-iframe');
    const previewLoading = document.getElementById('preview-loading');
    const matchingEntries = document.getElementById('matching-entries');
    const renderJsCheckbox = document.getElementById('render-js-checkbox');
    const fullPageLoading = document.getElementById('full-page-loading');
    const generateForm = document.getElementById('generate-form');
    const generateBtn = document.getElementById('generate-btn');

    let foundItems = [];
    let iframeFallback = false;
    let pageFullyLoaded = false;
    let iframeLoaded = false;

    // Listen for message from iframe to know when it's fully loaded
    window.addEventListener('message', function(event) {
        if (event.data === 'iframe-loaded') {
            console.log('Iframe content fully loaded');
            previewLoading.style.display = 'none';
            iframeLoaded = true;
            setTimeout(() => {
                try {
                    setupIframeInteractions();
                    hideFullPageLoading();
                } catch (e) {
                    console.error('Error processing iframe content:', e);
                    handleIframeAccessError();
                    hideFullPageLoading();
                }
            }, 500);
        }
    });

    // Function to hide the loading overlay when everything is ready
    function hideFullPageLoading() {
        if (pageFullyLoaded) return; // Only hide once

        // Add a small delay to ensure everything is rendered
        setTimeout(() => {
            fullPageLoading.style.opacity = '0';
            setTimeout(() => {
                fullPageLoading.style.display = 'none';
                pageFullyLoaded = true;
            }, 300); // Match transition duration
        }, 500);
    }

    // Update hidden input whenever the CSS selector changes
    cssSelector.addEventListener('input', function() {
        selectedCssPath.value = this.value;
        if (!iframeFallback) {
            clearAllHighlights();
            highlightMatchingElements(this.value);
        }
    });

    // Clear all highlights in the iframe
    function clearAllHighlights() {
        if (iframeFallback || !iframeLoaded) return;

        try {
            const iframe = previewIframe.contentDocument || previewIframe.contentWindow.document;

            // Remove all highlight classes
            iframe.querySelectorAll('.highlighted, .clicked-highlight, .similar-highlight').forEach(el => {
                el.classList.remove('highlighted', 'clicked-highlight', 'similar-highlight');
            });
        } catch (e) {
            console.error('Error clearing highlights:', e);
        }
    }

    // Handle iframe load event
    previewIframe.addEventListener('load', function() {
        previewLoading.style.display = 'none';
        iframeLoaded = true;

        try {
            // Try to highlight matching elements after iframe loads
            setTimeout(() => {
                try {
                    // Test access to iframe content
                    const doc = previewIframe.contentDocument || previewIframe.contentWindow.document;
                    const body = doc.body;

                    // Setup iframe interactions and event handlers
                    setupIframeInteractions();

                    // Hide the full page loading overlay
                    hideFullPageLoading();
                } catch (e) {
                    console.error('Cannot access iframe content:', e);
                    handleIframeAccessError();
                    hideFullPageLoading();
                }
            }, 1000);
        } catch (e) {
            console.error('Failed to highlight elements:', e);
            handleIframeAccessError();
            hideFullPageLoading();
        }
    });

    // Handle iframe error
    previewIframe.addEventListener('error', function() {
        handleIframeAccessError();
        hideFullPageLoading();
    });

    // Set a timeout to hide loading screen even if iframe doesn't load
    setTimeout(hideFullPageLoading, 8000);

    // Handle render JavaScript checkbox
    renderJsCheckbox.addEventListener('change', function() {
        if (iframeFallback) return;

        // Show loading overlay
        previewLoading.style.display = 'flex';
        fullPageLoading.style.display = 'flex';
        fullPageLoading.style.opacity = '1';
        iframeLoaded = false;
        pageFullyLoaded = false;

        // If checked, allow scripts
        if (this.checked) {
            previewIframe.sandbox = 'allow-same-origin allow-scripts';
        } else {
            previewIframe.sandbox = 'allow-same-origin';
        }

        // Reload iframe
        previewIframe.src = previewIframe.src;
    });

    // Setup interactions within the iframe
    function setupIframeInteractions() {
        if (iframeFallback || !iframeLoaded) return;

        try {
            const iframe = previewIframe.contentDocument || previewIframe.contentWindow.document;

            // Add CSS to prevent default behaviors and for highlighting
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                a { pointer-events: none !important; }
                .clicked-highlight {
                    outline: 2px solid #fd7e14 !important;
                    background-color: rgba(253, 126, 20, 0.1) !important;
                    position: relative;
                }
                .similar-highlight {
                    outline: 2px dashed #fd7e14 !important;
                    background-color: rgba(253, 126, 20, 0.05) !important;
                }
            `;
            iframe.head.appendChild(styleElement);

            // Prevent all link clicks in the iframe
            iframe.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Remove previous highlights
                clearAllHighlights();

                // Find the clicked element
                let element = e.target;
                if (!element || element.nodeName === '#text') return;

                // Highlight the clicked element
                element.classList.add('clicked-highlight');

                // Try to find parent div or article elements to find container
                let container = element;
                while (container && !['DIV', 'ARTICLE', 'SECTION', 'LI'].includes(container.tagName)) {
                    container = container.parentElement;
                }

                if (container) {
                    container.classList.add('clicked-highlight');
                    element = container;
                }

                // Get element info for selector
                const tagName = element.tagName.toLowerCase();
                let classes = Array.from(element.classList)
                    .filter(c => c !== 'clicked-highlight' && c !== 'highlighted' && c !== 'similar-highlight')
                    .join('.');

                // Generate selector for the element
                let elementSelector = tagName;
                if (classes) {
                    elementSelector += '.' + classes;
                } else if (element.id) {
                    elementSelector = '#' + element.id;
                }

                // Try to find similar elements
                try {
                    const similarElements = iframe.querySelectorAll(elementSelector);
                    if (similarElements.length > 0) {
                        similarElements.forEach(el => {
                            if (el !== element) {
                                el.classList.add('similar-highlight');
                            }
                        });

                        // Set this as the current selector
                        cssSelector.value = elementSelector;
                        selectedCssPath.value = elementSelector;

                        // Extract and display data from these elements
                        const items = [];
                        similarElements.forEach((el, index) => {
                            // Extract content for the matching entries panel
                            let title = el.querySelector('h1, h2, h3, h4, h5')?.textContent?.trim() ||
                                      el.querySelector('a')?.textContent?.trim() ||
                                      el.textContent?.trim().substring(0, 50) ||
                                      'Item ' + (index + 1);

                            let description = '';
                            const paragraphs = el.querySelectorAll('p');
                            if (paragraphs.length > 0) {
                                description = paragraphs[0].textContent?.trim() || '';
                            } else {
                                description = el.textContent?.trim().substring(0, 150) || '';
                            }

                            let link = '';
                            const anchor = el.querySelector('a');
                            if (anchor && anchor.href) {
                                link = anchor.href;
                            }

                            let image = '';
                            const img = el.querySelector('img');
                            if (img && img.src) {
                                image = img.src;
                            }

                            items.push({
                                title: title,
                                description: description.substring(0, 100) + (description.length > 100 ? '...' : ''),
                                link: link,
                                image: image
                            });
                        });

                        // Update the matching entries panel
                        updateMatchingEntriesWithItems(items);

                        // If selector looks promising, use it
                        if (similarElements.length > 1) {
                            console.log(`Found ${similarElements.length} similar elements with selector: ${elementSelector}`);
                        }
                    }
                } catch (e) {
                    console.error('Error finding similar elements:', e);
                }

                return false;
            }, true);
        } catch (e) {
            console.error('Error setting up iframe interactions:', e);
        }
    }

    // Update the matching entries panel with provided items
    function updateMatchingEntriesWithItems(items) {
        if (!items || items.length === 0) {
            matchingEntries.innerHTML = '<div class="text-center py-3 text-muted">No matching elements found</div>';
            return;
        }

        let html = '';
        items.forEach((item, index) => {
            html += `
                <div class="matching-entry" data-index="${index}">
                    <div class="matching-entry-title">${item.title}</div>
                    ${item.description ? `<div class="matching-entry-description">${item.description}</div>` : ''}
                    ${item.link ? `<div class="matching-entry-url">${item.link}</div>` : ''}
                </div>
            `;
        });

        matchingEntries.innerHTML = html;

        // Add click handlers for entries
        document.querySelectorAll('.matching-entry').forEach(entry => {
            entry.addEventListener('click', function() {
                // Remove selected class from all entries
                document.querySelectorAll('.matching-entry').forEach(e => {
                    e.classList.remove('selected-item');
                });

                // Add selected class to clicked entry
                this.classList.add('selected-item');

                // Scroll to corresponding element in iframe
                if (!iframeFallback) {
                    const index = parseInt(this.dataset.index);
                    try {
                        const iframe = previewIframe.contentDocument || previewIframe.contentWindow.document;
                        const elements = iframe.querySelectorAll(cssSelector.value);
                        if (elements[index]) {
                            elements[index].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    } catch (e) {
                        console.error('Error scrolling to element:', e);
                    }
                }
            });
        });
    }

    function handleIframeAccessError() {
        iframeFallback = true;

        // Show error message in iframe container
        const previewContainer = document.querySelector('.website-preview');
        previewContainer.innerHTML = `
            <div class="alert alert-danger m-3">
                <h5><i class="fas fa-exclamation-triangle me-2"></i> Không thể tải trực tiếp trang web</h5>
                <p>Do các hạn chế bảo mật của trình duyệt, chúng tôi không thể hiển thị nội dung trang web trong iframe.</p>
                <p>Vui lòng mở trang web trong một cửa sổ mới và sử dụng các ví dụ CSS selector:</p>
                <a href="${previewIframe.src}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-2"></i> Mở trang web
                </a>
            </div>
        `;

        // Show a simple list of examples in matching entries
        const exampleSelectors = [
            {title: 'Bài viết', selector: 'article', description: 'Chọn tất cả các thẻ article'},
            {title: 'Tin tức', selector: '.news-item', description: 'Chọn tất cả phần tử có class="news-item"'},
            {title: 'Tiêu đề bài viết', selector: '.post-title, h1, h2', description: 'Chọn tiêu đề các bài viết'},
            {title: 'Nội dung chính', selector: '#content, .main-content, .content', description: 'Chọn khu vực nội dung chính'}
        ];

        let html = '<div class="p-3"><h6>Các ví dụ selector phổ biến:</h6>';
        exampleSelectors.forEach(item => {
            html += `
                <div class="matching-entry" onclick="setSelector('${item.selector}')">
                    <div class="matching-entry-title">${item.title}</div>
                    <div><code>${item.selector}</code></div>
                    <div class="text-muted small">${item.description}</div>
                </div>
            `;
        });
        html += '</div>';

        matchingEntries.innerHTML = html;
    }

    // Function to highlight matching elements in the iframe
    function highlightMatchingElements(selector) {
        if (!selector || iframeFallback || !iframeLoaded) return;

        try {
            const iframe = previewIframe.contentDocument || previewIframe.contentWindow.document;

            // Remove previous highlights
            clearAllHighlights();

            // Find and highlight matching elements
            const elements = iframe.querySelectorAll(selector);
            foundItems = [];

            if (elements.length > 0) {
                elements.forEach((el, index) => {
                    el.classList.add('similar-highlight');

                    // Extract content for the matching entries panel
                    let title = el.querySelector('h1, h2, h3, h4, h5')?.textContent?.trim() ||
                               el.querySelector('a')?.textContent?.trim() ||
                               'Item ' + (index + 1);

                    let description = '';
                    const paragraphs = el.querySelectorAll('p');
                    if (paragraphs.length > 0) {
                        description = paragraphs[0].textContent?.trim() || '';
                    }

                    let link = '';
                    const anchor = el.querySelector('a');
                    if (anchor && anchor.href) {
                        link = anchor.href;
                    }

                    foundItems.push({
                        title: title,
                        description: description.substring(0, 100) + (description.length > 100 ? '...' : ''),
                        link: link
                    });
                });

                updateMatchingEntries();
            } else {
                matchingEntries.innerHTML = '<div class="text-center py-3 text-muted">No matching elements found</div>';
            }
        } catch (e) {
            console.error('Error highlighting elements:', e);
            handleIframeAccessError();
        }
    }

    // Update the matching entries panel
    function updateMatchingEntries() {
        if (foundItems.length === 0) {
            matchingEntries.innerHTML = '<div class="text-center py-3 text-muted">No matching elements found</div>';
            return;
        }

        let html = '';
        foundItems.forEach((item, index) => {
            html += `
                <div class="matching-entry" data-index="${index}">
                    <div class="matching-entry-title">${item.title}</div>
                    ${item.description ? `<div class="matching-entry-description">${item.description}</div>` : ''}
                    ${item.link ? `<div class="matching-entry-url">${item.link}</div>` : ''}
                </div>
            `;
        });

        matchingEntries.innerHTML = html;

        // Add click handlers for entries
        document.querySelectorAll('.matching-entry').forEach(entry => {
            entry.addEventListener('click', function() {
                // Remove selected class from all entries
                document.querySelectorAll('.matching-entry').forEach(e => {
                    e.classList.remove('selected-item');
                });

                // Add selected class to clicked entry
                this.classList.add('selected-item');

                if (iframeFallback) {
                    // In fallback mode, just set the selector
                    const index = parseInt(this.dataset.index);
                    if (index >= 0 && index < exampleSelectors.length) {
                        setSelector(exampleSelectors[index].selector);
                    }
                } else {
                    // Scroll to corresponding element in iframe
                    const index = parseInt(this.dataset.index);
                    try {
                        const iframe = previewIframe.contentDocument || previewIframe.contentWindow.document;
                        const elements = iframe.querySelectorAll(cssSelector.value);
                        if (elements[index]) {
                            elements[index].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    } catch (e) {
                        console.error('Error scrolling to element:', e);
                    }
                }
            });
        });
    }

    // Initial highlight after a short delay to ensure iframe is loaded
    setTimeout(() => {
        try {
            highlightMatchingElements(cssSelector.value);
        } catch (e) {
            console.error('Error in initial highlight:', e);
            handleIframeAccessError();
        }
    }, 2000);

    // Handle Generate RSS Feed form submission
    generateForm.addEventListener('submit', function(e) {
        // Make sure a CSS selector is specified
        if (!cssSelector.value.trim()) {
            e.preventDefault();
            alert('Please specify a CSS selector');
            return;
        }

        // Show loading overlay and disable submit button
        fullPageLoading.style.display = 'flex';
        fullPageLoading.style.opacity = '1';
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Generating...';
    });
});

// Function to set selector from examples
function setSelector(selector) {
    const cssSelector = document.getElementById('css-selector');
    const selectedCssPath = document.getElementById('selected-css-path');

    cssSelector.value = selector;
    selectedCssPath.value = selector;

    // Trigger input event to update highlighting
    const event = new Event('input');
    cssSelector.dispatchEvent(event);
}
</script>
@endsection
