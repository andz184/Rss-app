<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RSS Feed Generator - Visual Selector</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
            display: flex;
            align-items: center;
        }

        .js-toggle input {
            margin-right: 5px;
        }

        /* Main Content */
        .content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Left Panel - Website Preview */
        .website-panel {
            flex: 7; /* Increased from 5 to make preview larger */
            position: relative;
            overflow: hidden;
            border-right: 1px solid #dee2e6;
        }

        .web-preview {
            width: 100%;
            height: 100%;
            overflow: auto;
            transform-origin: top left;
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
            flex: 3; /* Keep control panel smaller */
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
            max-height: 500px;
            overflow-y: auto;
        }

        .entry-item {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 5px;
            transition: all 0.2s;
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
            color: #333;
            font-size: 14px;
        }

        .entry-url {
            font-size: 13px;
            color: #ff7846;
            word-break: break-all;
            padding: 5px 0;
        }

        .entry-description {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
            margin: 5px 0;
        }

        .entry-date {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        .entry-image {
            max-width: 100%;
            height: auto;
            max-height: 100px;
            margin-top: 5px;
            border-radius: 4px;
            object-fit: cover;
        }

        /* CSS Selector display */
        .css-selector-display {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .selector-text {
            flex: 1;
            font-family: monospace;
            font-size: 14px;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #ced4da;
            white-space: nowrap;
            overflow: auto;
        }

        .selector-copy {
            margin-left: 10px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
        }

        .selector-copy:hover {
            color: #ff7846;
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

        .generate-btn {
            padding: 10px 40px;
            background-color: #ff7846;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 16px;
        }

        .generate-btn:hover {
            background-color: #e26b3e;
        }

        .selected-count {
            color: #6c757d;
            font-size: 14px;
            margin-left: 15px;
        }

        /* Content type and mode sections */
        .content-type-section, .mode-section {
            margin-bottom: 15px;
        }

        .content-type-label, .mode-label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }

        .content-type-selector, .mode-selector {
            display: flex;
            align-items: center;
        }

        .content-type-select {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            cursor: pointer;
        }

        .mode-description {
            margin-top: 8px;
            font-size: 13px;
            color: #6c757d;
        }

        /* Instructions */
        .instructions {
            padding: 10px 15px;
            background-color: rgba(255, 120, 70, 0.1);
            border-left: 4px solid #ff7846;
            margin-bottom: 20px;
            font-size: 14px;
            color: #495057;
            border-radius: 4px;
        }

        .instructions ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .instructions li {
            margin-bottom: 5px;
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
            <input type="text" class="url-bar" id="url-input" value="{{ $url ?? 'https://vietnamnet.vn/dan-toc-ton-giao' }}" readonly>
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
                    <p>Đang tải trang web...</p>
                    <p>Vui lòng đợi trong khi hệ thống tải nội dung từ trang web.</p>
                </div>
                <iframe id="web-preview" class="web-preview" src="about:blank" sandbox="allow-same-origin allow-scripts"></iframe>
            </div>

            <!-- Control Panel -->
            <div class="control-panel">
                <div class="instructions">
                    <strong>Hướng dẫn:</strong>
                    <ul>
                        <li>Di chuột qua các phần tử trên trang web để xem trước selector</li>
                        <li>Click vào phần tử để chọn và tìm các phần tử tương tự</li>
                        <li>Các phần tử phù hợp sẽ được hiển thị ở bên phải</li>
                        <li>Khi hài lòng với lựa chọn, nhấn "Generate" để tạo RSS feed</li>
                    </ul>
                </div>

                <!-- Type of Content Section -->
                <div class="panel-section">
                    <div class="panel-body content-type-section">
                        <span class="content-type-label">Type of Content</span>
                        <div class="content-type-selector">
                            <select class="form-select">
                                <option value="news" selected>News</option>
                                <option value="blog">Blog</option>
                                <option value="videos">Videos</option>
                                <option value="products">Products</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- CSS Selector Section -->
                <div class="panel-section">
                    <div class="panel-header">
                        Title CSS Selector <span class="text-danger">*</span>
                        <button id="refresh-preview" title="Refresh preview" style="background: none; border: none; cursor: pointer;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="panel-body">
                        <div>Hover và click vào các phần tử trên trang để tự động tạo selector</div>
                        <div class="css-selector-display">
                            <div id="selector-text" class="selector-text">Hover và click vào phần tử trong trang</div>
                            <button id="selector-copy" class="selector-copy" title="Copy selector">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="hidden" id="css-selector" class="selector-input">
                    </div>
                </div>

                <!-- Matching Entries Section -->
                <div class="panel-section">
                    <div class="panel-header">
                        Matching entries <span id="items-count">(0)</span>
                    </div>
                    <div class="panel-body">
                        <div class="matching-entries" id="matching-entries">
                            <div class="entry-item">
                                <div class="entry-title">Hover và click vào phần tử trong trang để xem kết quả</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>
                <button class="generate-btn" id="generate-btn">Generate</button>
                <span class="selected-count">Selected <span id="selected-count">0</span> elements</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- RSS Selector Script -->
    <script src="{{ asset('js/rss-selector.js') }}"></script>
</body>
</html>

