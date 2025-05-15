<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web to RSS Creator</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Header Styles */
        .header {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .back-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #212529;
            display: flex;
            align-items: center;
            font-size: 14px;
            margin-right: 10px;
        }

        .url-bar {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: white;
        }

        .js-toggle {
            margin-left: 10px;
            font-size: 14px;
        }

        /* Main Content */
        .content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Left Panel - Website Preview */
        .website-panel {
            flex: 6;
            position: relative;
            overflow: hidden;
            border-right: 1px solid #dee2e6;
        }

        .web-preview {
            width: 100%;
            height: 100%;
            overflow: auto;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Right Panel - Controls */
        .control-panel {
            flex: 4;
            background-color: #f8f9fa;
            overflow-y: auto;
            padding: 15px;
        }

        .panel-section {
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

        .panel-header {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-body {
            padding: 15px;
        }

        /* Selector Input */
        .selector-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: monospace;
            margin-bottom: 10px;
        }

        /* Matching Entries */
        .matching-entries {
            max-height: 300px;
            overflow-y: auto;
        }

        .entry-item {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
        }

        .entry-item:hover {
            background-color: #f8f9fa;
        }

        .entry-item.selected {
            background-color: rgba(52, 152, 219, 0.1);
            border-left: 3px solid #3498db;
        }

        .entry-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .entry-url {
            font-size: 12px;
            color: #3498db;
            word-break: break-all;
        }

        .entry-summary {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .entry-date {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }

        /* Button Styles */
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        /* Footer */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-top: 1px solid #dee2e6;
            background-color: white;
        }

        .feed-title-input {
            display: flex;
            flex-direction: column;
        }

        .feed-title-input label {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .feed-title-input input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 300px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .generate-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }

        .selected-count {
            color: #6c757d;
            font-size: 14px;
        }

        /* Element highlighting */
        .highlight {
            outline: 2px solid #3498db !important;
            background-color: rgba(52, 152, 219, 0.1) !important;
        }

        .hover-highlight {
            outline: 2px dashed #3498db !important;
            background-color: rgba(52, 152, 219, 0.1) !important;
        }

        /* Required indicator */
        .required {
            color: #dc3545;
        }

        /* Mode selector */
        .mode-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .mode-selector .mode-option {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .mode-selector input {
            margin-right: 5px;
        }

        /* Selector suggestions */
        .selector-suggestions {
            margin-top: 10px;
        }

        .suggestion-item {
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #e9ecef;
        }

        .suggestion-title {
            font-weight: 500;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .suggestion-selector {
            font-family: monospace;
            font-size: 12px;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <button class="back-btn" id="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <input type="text" class="url-bar" id="url-input" value="{{ $url ?? 'https://example.com' }}" readonly>
            <div class="js-toggle">
                <input type="checkbox" id="js-toggle-checkbox" checked>
                <label for="js-toggle-checkbox">Render JavaScript</label>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <!-- Website Preview Panel -->
            <div class="website-panel">
                <div id="loading-overlay" class="loading-overlay">
                    <div class="spinner"></div>
                    <p>Loading website preview...</p>
                    <p>Please wait while we load content from the website.</p>
                </div>
                <iframe id="web-preview" class="web-preview" src="about:blank" sandbox="allow-same-origin allow-scripts"></iframe>
            </div>

            <!-- Control Panel -->
            <div class="control-panel">
                <!-- Chế độ chọn -->
                <div class="panel-section">
                    <div class="panel-header">
                        <i class="fas fa-cog"></i> Chế độ chọn RSS
                    </div>
                    <div class="panel-body">
                        <div class="mode-selector">
                            <label class="mode-option">
                                <input type="radio" name="mode" value="title-first" checked>
                                <span><i class="fas fa-magic"></i> Chọn tiêu đề trước</span>
                            </label>
                            <label class="mode-option">
                                <input type="radio" name="mode" value="auto">
                                <span><i class="fas fa-robot"></i> Tự động</span>
                            </label>
                            <label class="mode-option">
                                <input type="radio" name="mode" value="manual">
                                <span><i class="fas fa-sliders-h"></i> Thủ công</span>
                            </label>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle"></i> Chọn "Chọn tiêu đề trước" để click vào các tiêu đề trên trang, hệ thống sẽ tự động gợi ý các thành phần còn lại.
                        </small>
                    </div>
                </div>

                <!-- Title Selector -->
                <div class="panel-section title-selector-section">
                    <div class="panel-header">
                        <span>
                            <i class="fas fa-heading"></i> Tiêu đề
                            <span class="required">*</span>
                        </span>
                        <button id="title-inspector-btn" class="btn btn-sm btn-primary" title="Click để chọn tiêu đề từ trang">
                            <i class="fas fa-mouse-pointer"></i> Chọn tiêu đề
                        </button>
                    </div>
                    <div class="panel-body">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-code"></i></span>
                            <input type="text" id="title-selector" class="selector-input" placeholder="Click vào tiêu đề trên trang hoặc nhập CSS selector">
                        </div>

                        <div class="mb-3">
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" id="auto-detect-related" checked>
                                <label class="form-check-label" for="auto-detect-related">
                                    Tự động phát hiện link, mô tả và ngày
                                </label>
                            </div>
                            <small class="text-muted">Hệ thống sẽ tự động tìm các phần tử liên quan gần với tiêu đề.</small>
                        </div>

                        <div class="selector-suggestions">
                            <div class="suggestion-header mb-2">Gợi ý selector phổ biến:</div>
                            <div class="suggestion-item" data-target="title-selector" data-selector="h2 a">
                                <div class="suggestion-title">Thẻ h2 có chứa link</div>
                                <div class="suggestion-selector">h2 a</div>
                            </div>
                            <div class="suggestion-item" data-target="title-selector" data-selector=".entry-title">
                                <div class="suggestion-title">Class entry-title</div>
                                <div class="suggestion-selector">.entry-title</div>
                            </div>
                            <div class="suggestion-item" data-target="title-selector" data-selector=".post-title">
                                <div class="suggestion-title">Class post-title</div>
                                <div class="suggestion-selector">.post-title</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Container Selector (bị ẩn ban đầu) -->
                <div class="panel-section" id="container-selector-section" style="display:none;">
                    <div class="panel-header">
                        <span>
                            <i class="fas fa-th-large"></i> Khối tin
                            <span class="required">*</span>
                        </span>
                        <button id="container-inspector-btn" class="btn btn-sm btn-secondary" title="Click để chọn khối tin từ trang">
                            <i class="fas fa-mouse-pointer"></i> Chọn
                        </button>
                    </div>
                    <div class="panel-body">
                        <input type="text" id="container-selector" class="selector-input" placeholder="Click khối tin từ trang hoặc nhập CSS selector">
                        <small class="text-muted d-block mt-1">
                            Khối tin là phần tử chứa tiêu đề, link, mô tả và ngày của một bài viết.
                        </small>
                    </div>
                </div>

                <!-- Các selector phụ (Manual Mode) -->
                <div class="manual-selectors" style="display:none;">
                    <!-- Link Selector -->
                    <div class="panel-section">
                        <div class="panel-header">
                            <span><i class="fas fa-link"></i> Link</span>
                            <button class="select-element-btn btn btn-sm btn-secondary" data-target="link-selector">
                                <i class="fas fa-mouse-pointer"></i>
                            </button>
                        </div>
                        <div class="panel-body">
                            <input type="text" id="link-selector" class="selector-input" placeholder="CSS selector cho link (liên quan đến khối tin)">
                        </div>
                    </div>

                    <!-- Summary Selector -->
                    <div class="panel-section">
                        <div class="panel-header">
                            <span><i class="fas fa-align-left"></i> Mô tả</span>
                            <button class="select-element-btn btn btn-sm btn-secondary" data-target="summary-selector">
                                <i class="fas fa-mouse-pointer"></i>
                            </button>
                        </div>
                        <div class="panel-body">
                            <input type="text" id="summary-selector" class="selector-input" placeholder="CSS selector cho mô tả (liên quan đến khối tin)">
                        </div>
                    </div>

                    <!-- Date Selector -->
                    <div class="panel-section">
                        <div class="panel-header">
                            <span><i class="fas fa-calendar-alt"></i> Ngày</span>
                            <button class="select-element-btn btn btn-sm btn-secondary" data-target="date-selector">
                                <i class="fas fa-mouse-pointer"></i>
                            </button>
                        </div>
                        <div class="panel-body">
                            <input type="text" id="date-selector" class="selector-input" placeholder="CSS selector cho ngày (liên quan đến khối tin)">
                        </div>
                    </div>
                </div>

                <!-- Matching Entries Section -->
                <div class="panel-section">
                    <div class="panel-header">
                        <span>
                            <i class="fas fa-list"></i> Xem trước kết quả
                            <span id="items-count">(0 items)</span>
                        </span>
                        <button id="refresh-preview-btn" class="btn btn-sm btn-secondary">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="panel-body">
                        <div class="matching-entries" id="matching-entries">
                            <div class="entry-item">
                                <div class="entry-title">Chọn tiêu đề từ trang để xem danh sách</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="feed-title-input">
                <label for="feed-title">Feed Title <span class="required">*</span></label>
                <input type="text" id="feed-title" placeholder="Enter a title for your RSS feed">
            </div>

            <div class="actions">
                <button class="generate-btn" id="generate-btn">
                    <i class="fas fa-rss"></i> Generate RSS
                </button>
                <div class="selected-count">Selected <span id="selected-count">0</span> items</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // DOM elements
            const iframe = document.getElementById('web-preview');
            const loadingOverlay = document.getElementById('loading-overlay');
            const containerSelector = document.getElementById('container-selector');
            const containerSelectorSection = document.getElementById('container-selector-section');
            const titleSelector = document.getElementById('title-selector');
            const linkSelector = document.getElementById('link-selector');
            const summarySelector = document.getElementById('summary-selector');
            const dateSelector = document.getElementById('date-selector');
            const matchingEntries = document.getElementById('matching-entries');
            const selectedCount = document.getElementById('selected-count');
            const itemsCount = document.getElementById('items-count');
            const feedTitle = document.getElementById('feed-title');
            const generateBtn = document.getElementById('generate-btn');
            const backButton = document.getElementById('back-button');
            const titleInspectorBtn = document.getElementById('title-inspector-btn');
            const containerInspectorBtn = document.getElementById('container-inspector-btn');
            const refreshPreviewBtn = document.getElementById('refresh-preview-btn');
            const jsToggleCheckbox = document.getElementById('js-toggle-checkbox');
            const modeRadios = document.querySelectorAll('input[name="mode"]');
            const manualSelectors = document.querySelector('.manual-selectors');
            const autoDetectRelated = document.getElementById('auto-detect-related');
            const selectElementBtns = document.querySelectorAll('.select-element-btn');
            const suggestionItems = document.querySelectorAll('.suggestion-item');

            // State variables
            let currentMode = 'title-first';
            let selectedItems = [];
            let isInspectorActive = false;
            let currentTargetInput = null;
            let titleElements = [];
            let detectedContainerSelector = '';

            // URL and proxy settings
            const url = '{{ $url ?? "https://example.com" }}';
            const useJs = jsToggleCheckbox.checked;
            const proxyUrl = '{{ route("web-scraper.proxy") }}?url=' + encodeURIComponent(url) +
                            '&render_js=' + (useJs ? '1' : '0');

            // Load the iframe with the proxy URL
            iframe.src = proxyUrl;

            // Show loading overlay while iframe loads
            loadingOverlay.style.display = 'flex';

            // Hide loading overlay when iframe is loaded
            iframe.addEventListener('load', () => {
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                    setupIframeInteractions();
                }, 1000);
            });

            // Handle mode switching
            modeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    currentMode = this.value;

                    // Hiển thị/ẩn các thành phần tương ứng với từng chế độ
                    if (currentMode === 'manual') {
                        manualSelectors.style.display = 'block';
                        containerSelectorSection.style.display = 'block';
                        autoDetectRelated.parentElement.parentElement.style.display = 'none';
                    } else if (currentMode === 'auto') {
                        manualSelectors.style.display = 'none';
                        containerSelectorSection.style.display = 'block';
                        autoDetectRelated.parentElement.parentElement.style.display = 'none';
                    } else if (currentMode === 'title-first') {
                        manualSelectors.style.display = 'none';
                        containerSelectorSection.style.display = 'none';
                        autoDetectRelated.parentElement.parentElement.style.display = 'block';
                    }

                    // Cập nhật giao diện đã chọn
                    updatePreview();
                });
            });

            // Setup interactions with the iframe content
            function setupIframeInteractions() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                    // Add highlight styles to the iframe
                    const style = iframeDoc.createElement('style');
                    style.textContent = `
                        .hover-highlight {
                            outline: 2px dashed #3498db !important;
                            background-color: rgba(52, 152, 219, 0.1) !important;
                        }
                        .highlight {
                            outline: 2px solid #3498db !important;
                            background-color: rgba(52, 152, 219, 0.1) !important;
                        }
                        .title-highlight {
                            outline: 2px solid #e74c3c !important;
                            background-color: rgba(231, 76, 60, 0.1) !important;
                        }
                        * {
                            cursor: default;
                        }
                    `;
                    iframeDoc.head.appendChild(style);

                    // Setup global click handler for inspector mode
                    iframeDoc.addEventListener('click', function(e) {
                        if (!isInspectorActive) return;

                        e.preventDefault();
                        e.stopPropagation();

                        const element = e.target;

                        if (currentTargetInput) {
                            // Đang chọn thành phần phụ (link, summary, date)
                            const selector = getRelativeSelector(element);
                            currentTargetInput.value = selector;
                            currentTargetInput = null;

                            // Cập nhật xem trước
                            updatePreview();
                        } else if (currentMode === 'title-first') {
                            // Đang ở chế độ chọn tiêu đề trước
                            handleTitleSelection(element);
                        } else {
                            // Đang chọn khối tin
                            const bestContainer = findBestContainer(element);
                            const selector = generateSelector(bestContainer || element);
                            containerSelector.value = selector;

                            // Tìm và highlight tất cả các phần tử phù hợp
                            updateSelectedItems(selector);
                        }

                        // Thoát chế độ Inspector
                        isInspectorActive = false;
                        iframeDoc.body.style.cursor = 'default';
                        if (titleInspectorBtn.classList.contains('active')) {
                            titleInspectorBtn.classList.remove('active');
                        }
                        if (containerInspectorBtn.classList.contains('active')) {
                            containerInspectorBtn.classList.remove('active');
                        }
                    });

                    // Setup hover highlighting for inspector mode
                    iframeDoc.addEventListener('mouseover', function(e) {
                        if (!isInspectorActive) return;

                        const element = e.target;
                        element.classList.add('hover-highlight');
                    });

                    iframeDoc.addEventListener('mouseout', function(e) {
                        if (!isInspectorActive) return;

                        const element = e.target;
                        element.classList.remove('hover-highlight');
                    });
                } catch (error) {
                    console.error('Error setting up iframe interactions:', error);
                }
            }

            // Xử lý khi người dùng chọn một tiêu đề
            function handleTitleSelection(element) {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                    // Xóa tất cả highlight cũ
                    clearAllHighlights(iframeDoc);

                    // Tìm phần tử tiêu đề phù hợp nhất (h1-h6, a, strong, hoặc có class title)
                    const titleElement = findBestTitleElement(element);

                    // Tạo selector cho tiêu đề
                    const titleSel = generateSelector(titleElement);
                    titleSelector.value = titleSel;

                    // Highlight tiêu đề đã chọn
                    titleElement.classList.add('title-highlight');

                    // Tìm tất cả các tiêu đề tương tự
                    titleElements = Array.from(iframeDoc.querySelectorAll(titleSel));
                    titleElements.forEach(el => el.classList.add('title-highlight'));

                    // Nếu đã chọn tự động phát hiện các thành phần liên quan
                    if (autoDetectRelated.checked) {
                        // Tìm container bao quanh tiêu đề
                        detectedContainerSelector = detectContainerFromTitle(titleElement);

                        // Tìm các thành phần liên quan (link, summary, date)
                        detectRelatedElements(titleElement, detectedContainerSelector);
                    }

                    // Cập nhật xem trước
                    updatePreview();
                } catch (error) {
                    console.error('Error selecting title:', error);
                }
            }

            // Tìm phần tử tiêu đề phù hợp nhất
            function findBestTitleElement(element) {
                // Nếu là tiêu đề hoặc link, sử dụng luôn
                if (/^h[1-6]$/i.test(element.tagName) || element.tagName === 'A') {
                    return element;
                }

                // Kiểm tra xem có phải là con của tiêu đề
                const parentHeading = element.closest('h1, h2, h3, h4, h5, h6');
                if (parentHeading) {
                    return parentHeading;
                }

                // Kiểm tra xem có phải là con của link
                const parentLink = element.closest('a');
                if (parentLink) {
                    return parentLink;
                }

                // Kiểm tra các phần tử có class chứa title
                if (element.className && element.className.toLowerCase().includes('title')) {
                    return element;
                }

                // Tìm phần tử cha gần nhất có class chứa title
                const titleParent = element.closest('[class*="title"]');
                if (titleParent) {
                    return titleParent;
                }

                // Trả về phần tử được click nếu không tìm thấy gì tốt hơn
                return element;
            }

            // Tìm container dựa trên phần tử tiêu đề
            function detectContainerFromTitle(titleElement) {
                // Các thẻ container phổ biến
                const containerTags = ['article', 'div', 'li', 'section'];

                // Tìm container gần nhất
                let parent = titleElement.parentElement;
                let maxDepth = 3;  // Không tìm quá xa
                let depth = 0;

                while (parent && depth < maxDepth) {
                    if (containerTags.includes(parent.tagName.toLowerCase())) {
                        // Kiểm tra các class phổ biến
                        if (parent.className) {
                            const classNames = parent.className.toString().toLowerCase();
                            if (classNames.includes('item') ||
                                classNames.includes('post') ||
                                classNames.includes('article') ||
                                classNames.includes('entry') ||
                                classNames.includes('card')) {
                                return generateSelector(parent);
                            }
                        }
                    }
                    parent = parent.parentElement;
                    depth++;
                }

                // Nếu không tìm thấy container phù hợp, trả về phần tử cha trực tiếp
                return generateSelector(titleElement.parentElement);
            }

            // Tìm và thiết lập các thành phần liên quan (link, summary, date)
            function detectRelatedElements(titleElement, containerSelector) {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                    // Tìm container
                    let container = titleElement.closest(containerSelector.replace(/^\./, '.'));
                    if (!container) {
                        // Nếu không tìm thấy, sử dụng phần tử cha
                        container = titleElement.parentElement;
                    }

                    // Tìm link trong tiêu đề hoặc container
                    let linkEl = titleElement.tagName === 'A' ? titleElement : titleElement.querySelector('a');
                    if (!linkEl) {
                        linkEl = container.querySelector('a');
                    }

                    if (linkEl) {
                        // Tìm được link
                        linkSelector.value = 'a';
                    }

                    // Tìm mô tả (thường là thẻ p gần tiêu đề)
                    const paragraphs = container.querySelectorAll('p');
                    if (paragraphs.length > 0) {
                        summarySelector.value = 'p';
                    }

                    // Tìm ngày (thường có class date hoặc time)
                    const dateEl = container.querySelector('time, [class*="date"], [class*="time"]');
                    if (dateEl) {
                        dateSelector.value = dateEl.tagName.toLowerCase();
                        if (dateEl.className) {
                            const dateClass = dateEl.className.split(' ')[0];
                            dateSelector.value = '.' + dateClass;
                        }
                    }

                } catch (error) {
                    console.error('Error detecting related elements:', error);
                }
            }

            // Xóa tất cả các highlight
            function clearAllHighlights(doc) {
                const highlighted = doc.querySelectorAll('.highlight, .title-highlight, .hover-highlight');
                highlighted.forEach(el => {
                    el.classList.remove('highlight');
                    el.classList.remove('title-highlight');
                    el.classList.remove('hover-highlight');
                });
            }

            // Update selected items based on container selector
            function updateSelectedItems(containerSelector) {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                    // Clear previous highlights
                    const highlightedElements = iframeDoc.querySelectorAll('.highlight');
                    highlightedElements.forEach(el => el.classList.remove('highlight'));

                    // Find matching containers
                    const containers = iframeDoc.querySelectorAll(containerSelector);
                    selectedItems = Array.from(containers);

                    // Update count display
                    selectedCount.textContent = selectedItems.length;
                    itemsCount.textContent = `(${selectedItems.length} items)`;

                    // Highlight containers
                    selectedItems.forEach(container => {
                        container.classList.add('highlight');
                    });

                    // Update preview panel with detected items
                    updatePreview();
                } catch (error) {
                    console.error('Error updating selected items:', error);
                    selectedCount.textContent = '0';
                    itemsCount.textContent = '(0 items)';
                    matchingEntries.innerHTML = '<div class="entry-item"><div class="entry-title">Invalid selector</div></div>';
                }
            }

            // Update the matching entries preview panel
            function updatePreview() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                    let items = [];
                    let count = 0;

                    if (currentMode === 'title-first') {
                        // Chế độ chọn tiêu đề trước
                        if (titleElements.length > 0) {
                            items = extractItemsFromTitleElements();
                            count = titleElements.length;
                        }
                    } else if (currentMode === 'auto' || currentMode === 'manual') {
                        // Chế độ tự động hoặc thủ công
                        if (containerSelector.value) {
                            items = extractItemsFromContainers(containerSelector.value);
                            count = selectedItems.length;
                        }
                    }

                    // Cập nhật số lượng mục
                    selectedCount.textContent = count;
                    itemsCount.textContent = `(${count} items)`;

                    // Hiển thị danh sách mục
                    renderPreviewItems(items);

                } catch (error) {
                    console.error('Error updating preview:', error);
                }
            }

            // Trích xuất các mục từ danh sách các phần tử tiêu đề
            function extractItemsFromTitleElements() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    let items = [];

                    for (const titleEl of titleElements) {
                        let item = {
                            title: titleEl.textContent.trim(),
                            link: '',
                            summary: '',
                            date: ''
                        };

                        // Tìm container nếu có
                        let container = null;
                        if (detectedContainerSelector) {
                            container = titleEl.closest(detectedContainerSelector.replace(/^\./, '.'));
                        }

                        if (!container) {
                            container = titleEl.parentElement;
                        }

                        // Trích xuất link
                        if (titleEl.tagName === 'A') {
                            item.link = titleEl.href;
                        } else {
                            const linkEl = titleEl.querySelector('a') || container.querySelector('a');
                            if (linkEl) {
                                item.link = linkEl.href;
                            }
                        }

                        // Trích xuất mô tả
                        const summaryEl = container.querySelector('p, [class*="excerpt"], [class*="summary"], [class*="desc"]');
                        if (summaryEl) {
                            item.summary = summaryEl.textContent.trim();
                        }

                        // Trích xuất ngày
                        const dateEl = container.querySelector('time, [class*="date"], [class*="time"]');
                        if (dateEl) {
                            item.date = dateEl.textContent.trim();
                        }

                        items.push(item);
                    }

                    return items;
                } catch (error) {
                    console.error('Error extracting items from titles:', error);
                    return [];
                }
            }

            // Trích xuất các mục từ các container
            function extractItemsFromContainers(containerSel) {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    let items = [];

                    // Lấy tất cả các container
                    const containers = iframeDoc.querySelectorAll(containerSel);
                    selectedItems = Array.from(containers);

                    // Duyệt qua từng container
                    selectedItems.forEach(container => {
                        let item = {
                            title: '',
                            link: '',
                            summary: '',
                            date: ''
                        };

                        // Trích xuất tiêu đề theo selector hoặc tự động
                        if (currentMode === 'manual' && titleSelector.value) {
                            const titleEl = container.querySelector(titleSelector.value);
                            if (titleEl) {
                                item.title = titleEl.textContent.trim();
                            }
                        } else {
                            // Tự động tìm tiêu đề
                            const titleEl = container.querySelector('h1, h2, h3, h4, h5, h6, [class*="title"]');
                            if (titleEl) {
                                item.title = titleEl.textContent.trim();
                            }
                        }

                        // Trích xuất link
                        if (currentMode === 'manual' && linkSelector.value) {
                            const linkEl = container.querySelector(linkSelector.value);
                            if (linkEl && linkEl.href) {
                                item.link = linkEl.href;
                            }
                        } else {
                            // Tự động tìm link
                            const linkEl = container.querySelector('a');
                            if (linkEl) {
                                item.link = linkEl.href;
                            }
                        }

                        // Trích xuất mô tả
                        if (currentMode === 'manual' && summarySelector.value) {
                            const summaryEl = container.querySelector(summarySelector.value);
                            if (summaryEl) {
                                item.summary = summaryEl.textContent.trim();
                            }
                        } else {
                            // Tự động tìm mô tả
                            const summaryEl = container.querySelector('p, [class*="excerpt"], [class*="summary"], [class*="desc"]');
                            if (summaryEl) {
                                item.summary = summaryEl.textContent.trim();
                            }
                        }

                        // Trích xuất ngày
                        if (currentMode === 'manual' && dateSelector.value) {
                            const dateEl = container.querySelector(dateSelector.value);
                            if (dateEl) {
                                item.date = dateEl.textContent.trim();
                            }
                        } else {
                            // Tự động tìm ngày
                            const dateEl = container.querySelector('time, [class*="date"], [class*="time"]');
                            if (dateEl) {
                                item.date = dateEl.textContent.trim();
                            }
                        }

                        // Nếu không có tiêu đề, lấy text đầu tiên của container
                        if (!item.title) {
                            item.title = container.textContent.trim().substring(0, 100) + (container.textContent.length > 100 ? '...' : '');
                        }

                        items.push(item);
                    });

                    return items;
                } catch (error) {
                    console.error('Error extracting items from containers:', error);
                    return [];
                }
            }

            // Hiển thị danh sách mục xem trước
            function renderPreviewItems(items) {
                if (items.length === 0) {
                    matchingEntries.innerHTML = `
                        <div class="entry-item">
                            <div class="entry-title">Chưa có mục nào được chọn</div>
                            <div class="text-muted small">Hãy chọn tiêu đề hoặc khối tin từ trang web</div>
                        </div>
                    `;
                    return;
                }

                let entriesHTML = '';

                items.forEach((item, index) => {
                    entriesHTML += `
                        <div class="entry-item" data-index="${index}">
                            <div class="entry-title">${item.title || 'Không có tiêu đề'}</div>
                            ${item.link ? `<div class="entry-url">${item.link}</div>` : ''}
                            ${item.summary ? `<div class="entry-summary">${item.summary.substring(0, 100)}${item.summary.length > 100 ? '...' : ''}</div>` : ''}
                            ${item.date ? `<div class="entry-date">${item.date}</div>` : ''}
                        </div>
                    `;
                });

                matchingEntries.innerHTML = entriesHTML;

                // Thêm xử lý sự kiện click vào các mục
                const entryItems = matchingEntries.querySelectorAll('.entry-item');
                entryItems.forEach(item => {
                    item.addEventListener('click', () => {
                        // Bỏ chọn tất cả các mục khác
                        entryItems.forEach(i => i.classList.remove('selected'));

                        // Chọn mục hiện tại
                        item.classList.add('selected');

                        // Cuộn đến phần tử tương ứng
                        try {
                            const index = parseInt(item.dataset.index);
                            let targetElement;

                            if (currentMode === 'title-first' && titleElements[index]) {
                                targetElement = titleElements[index];
                            } else if (selectedItems[index]) {
                                targetElement = selectedItems[index];
                            }

                            if (targetElement) {
                                targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        } catch (error) {
                            console.error('Error scrolling to element:', error);
                        }
                    });
                });
            }

            // Title inspector button
            titleInspectorBtn.addEventListener('click', () => {
                isInspectorActive = true;
                currentTargetInput = null; // Không nhắm vào input cụ thể
                titleInspectorBtn.classList.add('active');

                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    iframeDoc.body.style.cursor = 'crosshair';
                } catch (error) {
                    console.error('Error activating title inspector:', error);
                }
            });

            // Container inspector button
            containerInspectorBtn.addEventListener('click', () => {
                isInspectorActive = true;
                currentTargetInput = null; // Không nhắm vào input cụ thể
                containerInspectorBtn.classList.add('active');

                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    iframeDoc.body.style.cursor = 'crosshair';
                } catch (error) {
                    console.error('Error activating container inspector:', error);
                }
            });

            // Refresh preview button
            refreshPreviewBtn.addEventListener('click', () => {
                updatePreview();
            });

            // Auto-detect related checkbox
            autoDetectRelated.addEventListener('change', () => {
                if (autoDetectRelated.checked && titleElements.length > 0) {
                    // Nếu đã có tiêu đề được chọn, tự động phát hiện lại các thành phần liên quan
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        const titleElement = titleElements[0];

                        // Tìm container bao quanh tiêu đề
                        detectedContainerSelector = detectContainerFromTitle(titleElement);

                        // Tìm các thành phần liên quan (link, summary, date)
                        detectRelatedElements(titleElement, detectedContainerSelector);

                        // Cập nhật xem trước
                        updatePreview();
                    } catch (error) {
                        console.error('Error auto-detecting related elements:', error);
                    }
                }
            });

            // JavaScript rendering toggle
            jsToggleCheckbox.addEventListener('change', () => {
                // Reload iframe with new JS rendering setting
                const useJs = jsToggleCheckbox.checked;
                const newProxyUrl = '{{ route("web-scraper.proxy") }}?url=' + encodeURIComponent(url) +
                                   '&render_js=' + (useJs ? '1' : '0');

                loadingOverlay.style.display = 'flex';
                iframe.src = newProxyUrl;
            });

            // Element selector buttons
            selectElementBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    isInspectorActive = true;
                    const targetId = btn.dataset.target;
                    currentTargetInput = document.getElementById(targetId);

                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.body.style.cursor = 'crosshair';
                    } catch (error) {
                        console.error('Error activating element selector:', error);
                    }
                });
            });

            // Handle container selector input change
            containerSelector.addEventListener('input', () => {
                if (containerSelector.value.trim() !== '') {
                    updateSelectedItems(containerSelector.value);
                }
            });

            // Handle selector suggestions
            suggestionItems.forEach(item => {
                item.addEventListener('click', () => {
                    const selector = item.dataset.selector;
                    const targetInput = item.dataset.target;

                    if (targetInput === 'title-selector') {
                        titleSelector.value = selector;

                        try {
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            clearAllHighlights(iframeDoc);

                            // Tìm và highlight tất cả các tiêu đề phù hợp
                            titleElements = Array.from(iframeDoc.querySelectorAll(selector));
                            titleElements.forEach(el => el.classList.add('title-highlight'));

                            // Tự động phát hiện container và các thành phần liên quan
                            if (autoDetectRelated.checked && titleElements.length > 0) {
                                detectedContainerSelector = detectContainerFromTitle(titleElements[0]);
                                detectRelatedElements(titleElements[0], detectedContainerSelector);
                            }

                            updatePreview();
                        } catch (error) {
                            console.error('Error processing title selector suggestion:', error);
                        }
                    } else if (targetInput === 'container-selector') {
                        containerSelector.value = selector;
                        updateSelectedItems(selector);
                    }
                });
            });

            // Find the best container element for an item
            function findBestContainer(element) {
                // Common item container tags and classes
                const containerTags = ['article', 'li', 'div', 'section'];
                const containerClasses = ['item', 'post', 'news', 'card', 'article', 'entry'];

                // Check if the element itself is a good container
                if (containerTags.includes(element.tagName.toLowerCase())) {
                    if (element.className) {
                        const classList = element.className.toString().split(' ');
                        for (const cls of classList) {
                            for (const containerCls of containerClasses) {
                                if (cls.toLowerCase().includes(containerCls)) {
                                    return element;
                                }
                            }
                        }
                    }
                }

                // Check parent elements
                let parent = element.parentElement;
                let depth = 0;
                const maxDepth = 5;

                while (parent && depth < maxDepth) {
                    if (containerTags.includes(parent.tagName.toLowerCase())) {
                        if (parent.className) {
                            const classList = parent.className.toString().split(' ');
                            for (const cls of classList) {
                                for (const containerCls of containerClasses) {
                                    if (cls.toLowerCase().includes(containerCls)) {
                                        return parent;
                                    }
                                }
                            }
                        }
                    }

                    parent = parent.parentElement;
                    depth++;
                }

                return null;
            }

            // Generate a CSS selector for an element
            function generateSelector(element) {
                if (!element) return '';

                // Try ID selector
                if (element.id) {
                    return `#${element.id}`;
                }

                // Try class selector
                if (element.className && typeof element.className === 'string') {
                    const classes = element.className.split(' ')
                        .filter(c => c.trim() !== '')
                        .map(c => c.trim());

                    if (classes.length > 0) {
                        // Use first significant class
                        return `.${classes[0]}`;
                    }
                }

                // Use tag name
                return element.tagName.toLowerCase();
            }

            // Get a relative selector (from container to element)
            function getRelativeSelector(element) {
                // For simplicity, just use the tag name or most significant attribute
                if (element.id) {
                    return `#${element.id}`;
                }

                if (element.className && typeof element.className === 'string') {
                    const classes = element.className.split(' ')
                        .filter(c => c.trim() !== '')
                        .map(c => c.trim());

                    if (classes.length > 0) {
                        return `.${classes[0]}`;
                    }
                }

                return element.tagName.toLowerCase();
            }

            // Back button
            backButton.addEventListener('click', () => {
                window.location.href = '{{ route("web-scraper.index") }}';
            });

            // Generate button - Updated for new modes
            generateBtn.addEventListener('click', () => {
                // Validate required fields based on mode
                let isValid = true;
                let errorMessage = '';

                if (currentMode === 'title-first') {
                    if (!titleSelector.value) {
                        isValid = false;
                        errorMessage = 'Vui lòng chọn tiêu đề từ trang web';
                    }
                } else if (currentMode === 'auto' || currentMode === 'manual') {
                    if (!containerSelector.value) {
                        isValid = false;
                        errorMessage = 'Vui lòng chọn khối tin từ trang web';
                    }
                }

                if (!feedTitle.value) {
                    isValid = false;
                    errorMessage = errorMessage ?
                        errorMessage + ' và nhập tiêu đề cho feed' :
                        'Vui lòng nhập tiêu đề cho feed';
                }

                if (!isValid) {
                    alert(errorMessage);
                    return;
                }

                // Create form for submission
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("web-scraper.generate-rss") }}';

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add URL input
                const urlInput = document.createElement('input');
                urlInput.type = 'hidden';
                urlInput.name = 'url';
                urlInput.value = url;
                form.appendChild(urlInput);

                // Add feed title input
                const feedTitleInput = document.createElement('input');
                feedTitleInput.type = 'hidden';
                feedTitleInput.name = 'feed_title';
                feedTitleInput.value = feedTitle.value;
                form.appendChild(feedTitleInput);

                // Add content type input
                const contentTypeInput = document.createElement('input');
                contentTypeInput.type = 'hidden';
                contentTypeInput.name = 'content_type';
                contentTypeInput.value = 'news';
                form.appendChild(contentTypeInput);

                // Add mode input
                const modeInput = document.createElement('input');
                modeInput.type = 'hidden';
                modeInput.name = 'mode';
                modeInput.value = currentMode;
                form.appendChild(modeInput);

                // Thêm selector cho từng chế độ
                if (currentMode === 'title-first') {
                    // Trường hợp chọn tiêu đề trước
                    const titleSelectorInput = document.createElement('input');
                    titleSelectorInput.type = 'hidden';
                    titleSelectorInput.name = 'css_selector';
                    titleSelectorInput.value = titleSelector.value;
                    form.appendChild(titleSelectorInput);

                    // Trường hợp có auto-detect và phát hiện được container
                    if (autoDetectRelated.checked && detectedContainerSelector) {
                        const containerSelectorInput = document.createElement('input');
                        containerSelectorInput.type = 'hidden';
                        containerSelectorInput.name = 'container_selector';
                        containerSelectorInput.value = detectedContainerSelector;
                        form.appendChild(containerSelectorInput);
                    }

                    // Thêm các selector phụ nếu có
                    if (linkSelector.value) {
                        const linkSelectorInput = document.createElement('input');
                        linkSelectorInput.type = 'hidden';
                        linkSelectorInput.name = 'link_selector';
                        linkSelectorInput.value = linkSelector.value;
                        form.appendChild(linkSelectorInput);
                    }

                    if (summarySelector.value) {
                        const summarySelectorInput = document.createElement('input');
                        summarySelectorInput.type = 'hidden';
                        summarySelectorInput.name = 'summary_selector';
                        summarySelectorInput.value = summarySelector.value;
                        form.appendChild(summarySelectorInput);
                    }

                    if (dateSelector.value) {
                        const dateSelectorInput = document.createElement('input');
                        dateSelectorInput.type = 'hidden';
                        dateSelectorInput.name = 'date_selector';
                        dateSelectorInput.value = dateSelector.value;
                        form.appendChild(dateSelectorInput);
                    }
                } else {
                    // Các chế độ khác
                    const containerSelectorInput = document.createElement('input');
                    containerSelectorInput.type = 'hidden';
                    containerSelectorInput.name = 'css_selector';
                    containerSelectorInput.value = containerSelector.value;
                    form.appendChild(containerSelectorInput);

                    // Nếu là chế độ manual, thêm các selector phụ
                    if (currentMode === 'manual') {
                        if (titleSelector.value) {
                            const titleSelectorInput = document.createElement('input');
                            titleSelectorInput.type = 'hidden';
                            titleSelectorInput.name = 'title_selector';
                            titleSelectorInput.value = titleSelector.value;
                            form.appendChild(titleSelectorInput);
                        }

                        if (linkSelector.value) {
                            const linkSelectorInput = document.createElement('input');
                            linkSelectorInput.type = 'hidden';
                            linkSelectorInput.name = 'link_selector';
                            linkSelectorInput.value = linkSelector.value;
                            form.appendChild(linkSelectorInput);
                        }

                        if (summarySelector.value) {
                            const summarySelectorInput = document.createElement('input');
                            summarySelectorInput.type = 'hidden';
                            summarySelectorInput.name = 'summary_selector';
                            summarySelectorInput.value = summarySelector.value;
                            form.appendChild(summarySelectorInput);
                        }

                        if (dateSelector.value) {
                            const dateSelectorInput = document.createElement('input');
                            dateSelectorInput.type = 'hidden';
                            dateSelectorInput.name = 'date_selector';
                            dateSelectorInput.value = dateSelector.value;
                            form.appendChild(dateSelectorInput);
                        }
                    }
                }

                // Submit the form
                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
</body>
</html>
