<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Web to RSS - Selectors</title>

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
            flex: 7;
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
            border-top: 4px solid #ff7846;
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
            flex: 3;
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

        /* Selector Info */
        .selector-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .selector-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selector-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: monospace;
        }

        .refresh-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 16px;
        }

        .refresh-btn:hover {
            color: #ff7846;
        }

        .selector-help {
            font-size: 13px;
            color: #6c757d;
        }

        /* Matching Entries */
        .matching-entries {
            max-height: 500px;
            overflow-y: auto;
        }

        .entry-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
        }

        .entry-item:hover {
            background-color: #f8f9fa;
        }

        .entry-item.selected {
            background-color: rgba(255, 120, 70, 0.1);
            border-left: 3px solid #ff7846;
        }

        .entry-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .entry-url {
            font-size: 12px;
            color: #4a6cf7;
            word-break: break-all;
            margin-bottom: 8px;
        }

        .entry-description {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .entry-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }

        .entry-date {
            font-size: 12px;
            color: #6c757d;
        }

        /* Selector Examples */
        .selector-examples {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }

        .example-item {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.2s;
            background-color: #f9f9f9;
        }

        .example-item:hover {
            border-color: #ff7846;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .example-title {
            margin: 0 0 5px 0;
            font-weight: 500;
            font-size: 14px;
        }

        .example-code {
            display: block;
            padding: 5px;
            background: #f0f0f0;
            border-radius: 3px;
            color: #d63384;
            font-family: monospace;
            font-size: 13px;
        }

        /* Feed Generation Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 8px;
            width: 600px;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-weight: 600;
            font-size: 18px;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6c757d;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .form-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        /* Mode Selectors */
        .mode-select {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .mode-option {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .mode-option.active {
            background-color: rgba(255, 120, 70, 0.1);
            border-color: #ff7846;
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

        .status-info {
            display: flex;
            gap: 15px;
            color: #6c757d;
            font-size: 14px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .generate-btn {
            padding: 10px 20px;
            background-color: #ff7846;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-secondary {
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Element highlighting */
        .highlight {
            outline: 2px solid #ff7846 !important;
            background-color: rgba(255, 120, 70, 0.1) !important;
        }

        .hover-highlight {
            outline: 2px dashed #ff7846 !important;
            background-color: rgba(255, 120, 70, 0.1) !important;
        }

        /* Required indicator */
        .required {
            color: #dc3545;
        }

        /* Selector Code and Mapping */
        .selector-code {
            font-family: monospace;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            max-height: 150px;
            overflow-x: auto;
            white-space: nowrap;
            font-size: 13px;
        }

        .selector-mapping {
            margin-top: 10px;
            font-size: 13px;
        }

        .mapping-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .mapping-label {
            width: 80px;
            color: #666;
            font-weight: 500;
        }

        .mapping-value {
            flex: 1;
            font-family: monospace;
            overflow-x: auto;
            white-space: nowrap;
            padding: 3px 6px;
            background-color: #f8f8f8;
            border-radius: 3px;
            font-size: 12px;
        }

        .instructions-box {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .reset-btn {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 16px;
        }

        .reset-btn:hover {
            color: #ff7846;
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
            <input type="text" class="url-bar" id="url-input" value="{{ $url ?? 'https://baomoi.com/' }}" readonly>
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
                </div>
                <iframe id="web-preview" class="web-preview" src="about:blank" sandbox="allow-same-origin allow-scripts"></iframe>
            </div>

            <!-- Control Panel -->
            <div class="control-panel">
                <!-- CSS Selector Section -->
                <div class="panel-section">
                    <div class="panel-header">
                        CSS Selector <span class="required">*</span>
                    </div>
                    <div class="panel-body">
                        <div class="instructions-box">
                            <p><i class="fas fa-info-circle"></i> Bạn có thể chọn <strong>bất kỳ phần tử nào</strong> trên trang web bằng cách nhấp chuột vào nó.</p>
                            <p>Hệ thống sẽ tự động nhận dạng các phần tử tương tự để tạo RSS feed.</p>
                        </div>

                        <div class="selector-info">
                            <div class="selector-box">
                                <input type="text" id="css-selector" class="selector-input" placeholder="Click elements in the preview to select">
                                <button id="refresh-preview" class="refresh-btn" title="Refresh preview with this selector">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button id="reset-selection" class="reset-btn" title="Reset current selection">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="selector-help">
                                <i class="fas fa-lightbulb"></i> Mẹo: Chọn phần tử nào có cấu trúc lặp lại, như bài viết, tin tức, sản phẩm, v.v.
                            </div>
                        </div>

                        <div class="selector-examples">
                            <div class="example-item" data-selector="article">
                                <h6 class="example-title">Articles</h6>
                                <code class="example-code">article</code>
                            </div>
                            <div class="example-item" data-selector=".news-item">
                                <h6 class="example-title">News items</h6>
                                <code class="example-code">.news-item</code>
                            </div>
                            <div class="example-item" data-selector=".post, .entry">
                                <h6 class="example-title">Posts or Entries</h6>
                                <code class="example-code">.post, .entry</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matching Entries Section -->
                <div class="panel-section">
                    <div class="panel-header">
                        Matching entries preview
                    </div>
                    <div class="panel-body">
                        <div class="matching-entries" id="matching-entries">
                            <div class="entry-item">
                                <div class="entry-title">Click an element in the preview to see matching items</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Selectors Section -->
                <div class="panel-section">
                    <div class="panel-header">
                        Selector Information
                    </div>
                    <div class="panel-body">
                        <div id="selector-text" class="selector-code">Click on an element to generate selector</div>
                        <div class="selector-mapping" id="selector-mapping">
                            <!-- Dynamic mapping content will be shown here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="status-info">
                <div>Selected: <span id="selected-count">0</span> elements</div>
                <div>Items in feed: <span id="items-count">0</span></div>
            </div>

            <div class="actions">
                <button class="generate-btn" id="generate-btn">Generate RSS Feed</button>
            </div>
        </div>
    </div>

    <!-- Feed Generation Modal -->
    <div id="feed-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configure RSS Feed</h5>
                <button type="button" class="modal-close" id="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="feed-form" action="{{ route('web-scraper.generate-popup') }}" method="POST">
                    @csrf
                    <input type="hidden" name="css_selector" id="form-selector">
                    <input type="hidden" name="url" id="form-url" value="{{ $url ?? '' }}">

                    <div class="form-group">
                        <label for="feed-title">Feed Title <span class="required">*</span></label>
                        <input type="text" class="form-control" id="feed-title" name="feed_title" placeholder="Enter a title for your RSS feed" required>
                    </div>

                    <div class="form-group">
                        <label for="feed-description">Feed Description</label>
                        <textarea class="form-control" id="feed-description" name="feed_description" rows="2" placeholder="Enter a description for your RSS feed"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Content Type</label>
                        <div class="mode-select">
                            <div class="mode-option active" data-value="news">
                                <i class="fas fa-newspaper"></i> News
                            </div>
                            <div class="mode-option" data-value="blog">
                                <i class="fas fa-blog"></i> Blog
                            </div>
                            <div class="mode-option" data-value="videos">
                                <i class="fas fa-video"></i> Videos
                            </div>
                            <div class="mode-option" data-value="products">
                                <i class="fas fa-shopping-cart"></i> Products
                            </div>
                        </div>
                        <input type="hidden" name="content_type" id="content-type" value="news">
                    </div>

                    <div class="form-group">
                        <label>Options</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="full-content" name="full_content" value="1">
                            <label for="full-content">Include full content</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="include-images" name="include_images" value="1" checked>
                            <label for="include-images">Include images</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="update-frequency">Update Frequency</label>
                        <select class="form-control" id="update-frequency" name="update_frequency">
                            <option value="15">Every 15 minutes</option>
                            <option value="30">Every 30 minutes</option>
                            <option value="60" selected>Every hour</option>
                            <option value="180">Every 3 hours</option>
                            <option value="360">Every 6 hours</option>
                            <option value="720">Every 12 hours</option>
                            <option value="1440">Every day</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="items-limit">Items Limit</label>
                        <select class="form-control" id="items-limit" name="items_limit">
                            <option value="10">10 items</option>
                            <option value="20" selected>20 items</option>
                            <option value="50">50 items</option>
                            <option value="100">100 items</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancel-btn">Cancel</button>
                <button type="button" class="generate-btn" id="submit-feed">Create RSS Feed</button>
            </div>
        </div>
    </div>

    <!-- Include external JavaScript file -->
    <script src="{{ asset('js/rss-selector.js') }}"></script>
</body>
</html>
