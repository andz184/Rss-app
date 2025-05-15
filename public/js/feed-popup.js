/**
 * Feed Popup Handler
 *
 * This script handles the popup functionality when clicking on URLs in the web scraper page.
 * It allows users to create a new feed from a URL with selector options.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Function to open the popup
    window.openFeedPopup = function(url) {
        // Create popup overlay
        const overlay = document.createElement('div');
        overlay.className = 'scraper-overlay';
        document.body.appendChild(overlay);

        // Prevent body scrolling
        document.body.style.overflow = 'hidden';

        // Create popup container
        const popupContainer = document.createElement('div');
        popupContainer.className = 'scraper-popup-container';
        overlay.appendChild(popupContainer);

        // Create popup header
        const header = document.createElement('div');
        header.className = 'scraper-header';
        header.innerHTML = `
            <button class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <input type="text" class="url-bar" value="${url}" readonly>
            <div class="js-toggle">
                <input type="checkbox" id="render-js" checked>
                <label for="render-js">Render JavaScript</label>
            </div>
        `;
        popupContainer.appendChild(header);

        // Create content area with iframe and control panel
        const content = document.createElement('div');
        content.className = 'scraper-content';
        content.innerHTML = `
            <div class="website-panel">
                <div class="loading-overlay">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Loading website content...</div>
                    <div class="loading-progress">
                        <div class="loading-progress-bar">
                            <div class="loading-progress-fill"></div>
                        </div>
                        <div class="loading-info">Please wait while we load the website</div>
                    </div>
                </div>
                <iframe id="web-preview" class="web-preview" sandbox="allow-same-origin"></iframe>
            </div>
            <div class="control-panel">
                <div class="panel-section">
                    <div class="panel-header">Type of Content</div>
                    <div class="panel-body">
                        <select class="form-select" id="content-type">
                            <option value="news" selected>News</option>
                            <option value="blog">Blog</option>
                            <option value="videos">Videos</option>
                            <option value="products">Products</option>
                        </select>
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-header">Mode</div>
                    <div class="panel-body">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="mode" id="auto-mode" value="auto" checked>
                            <label class="form-check-label" for="auto-mode">Auto</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="mode" id="manual-mode" value="manual">
                            <label class="form-check-label" for="manual-mode">Manual</label>
                        </div>
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-header">Title CSS Selector <span class="text-danger">*</span></div>
                    <div class="panel-body">
                        <input type="text" class="selector-input" id="title-selector" placeholder="div > div > div > div > div">
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-header d-flex justify-content-between align-items-center">
                        <span>Matching entries</span>
                        <button class="btn btn-sm btn-link p-0" id="toggle-entries"><i class="fas fa-chevron-up"></i></button>
                    </div>
                    <div class="panel-body">
                        <div class="matching-entries" id="matching-entries"></div>
                    </div>
                </div>

                <div class="panel-section">
                    <div class="panel-header">Feed Name <span class="text-danger">*</span></div>
                    <div class="panel-body">
                        <input type="text" class="form-control" id="feed-title" placeholder="Enter a name for this feed">
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-primary" id="generate-btn">Generate</button>
                </div>
            </div>
        `;
        popupContainer.appendChild(content);

        // Add event listeners
        const backBtn = header.querySelector('.back-btn');
        backBtn.addEventListener('click', function() {
            document.body.style.overflow = '';
            overlay.remove();
        });

        // Toggle entries section
        const toggleBtn = content.querySelector('#toggle-entries');
        const entriesSection = content.querySelector('.matching-entries');
        toggleBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-chevron-up')) {
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                entriesSection.style.maxHeight = '0';
            } else {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                entriesSection.style.maxHeight = '250px';
            }
        });

        // Load content in iframe
        const iframe = content.querySelector('#web-preview');
        const loadingOverlay = content.querySelector('.loading-overlay');

        // Function to load URL content in iframe
        function loadIframeContent(targetUrl) {
            fetch('/web-scraper/fetch-html', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ url: targetUrl })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Write the HTML content to the iframe
                    iframe.srcdoc = data.html;

                    // Hide loading overlay when iframe is loaded
                    iframe.onload = function() {
                        loadingOverlay.style.opacity = '0';
                        setTimeout(() => {
                            loadingOverlay.style.display = 'none';
                        }, 500);

                        // Add hover highlighting to elements in the iframe
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        findMatchingElements(iframeDoc);
                    };
                } else {
                    console.error('Error loading content:', data.error);
                    loadingOverlay.querySelector('.loading-text').textContent = 'Error loading content';
                    loadingOverlay.querySelector('.loading-info').textContent = data.error;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loadingOverlay.querySelector('.loading-text').textContent = 'Error loading content';
                loadingOverlay.querySelector('.loading-info').textContent = 'Network error, please try again';
            });
        }

        // Function to find and display matching elements
        function findMatchingElements(doc) {
            const titleSelector = document.getElementById('title-selector').value;
            if (!titleSelector) return;

            const matchingElements = doc.querySelectorAll(titleSelector);
            const entriesContainer = document.getElementById('matching-entries');
            entriesContainer.innerHTML = '';

            if (matchingElements.length === 0) {
                entriesContainer.innerHTML = '<div class="text-muted">No elements found matching this selector</div>';
                return;
            }

            // Update the matching entries display
            matchingElements.forEach((el, index) => {
                if (index >= 25) return; // Limit to 25 entries

                const entryItem = document.createElement('div');
                entryItem.className = 'entry-item';

                const title = el.textContent.trim();
                const url = el.closest('a') ? el.closest('a').href : '';

                entryItem.innerHTML = `
                    <div class="entry-title">${title || 'Untitled Element'}</div>
                    ${url ? `<div class="entry-url">${url}</div>` : ''}
                `;

                entriesContainer.appendChild(entryItem);
            });

            // Update counter
            const selectedCount = Math.min(matchingElements.length, 25);
            document.getElementById('generate-btn').textContent = `Generate (${selectedCount} items)`;
        }

        // Initialize by loading the iframe content
        loadIframeContent(url);

        // Event listener for CSS selector input
        const titleSelectorInput = document.getElementById('title-selector');
        titleSelectorInput.addEventListener('input', function() {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            findMatchingElements(iframeDoc);
        });

        // Event listener for generate button
        const generateBtn = document.getElementById('generate-btn');
        generateBtn.addEventListener('click', function() {
            const feedTitle = document.getElementById('feed-title').value;
            const titleSelector = document.getElementById('title-selector').value;
            const contentType = document.getElementById('content-type').value;
            const mode = document.querySelector('input[name="mode"]:checked').value;

            if (!feedTitle) {
                alert('Please enter a feed title');
                return;
            }

            if (!titleSelector) {
                alert('Please enter a CSS selector');
                return;
            }

            // Send data to server to generate feed
            fetch('/web-scraper/generate-popup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    feed_title: feedTitle,
                    css_selector: titleSelector,
                    title_selector: titleSelector,
                    content_type: contentType,
                    url: url
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close popup and redirect to the new feed
                    document.body.style.overflow = '';
                    overlay.remove();
                    window.location.href = data.redirect_url;
                } else {
                    alert('Error generating feed: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error, please try again');
            });
        });
    };

    // Add click event to URL links on the page
    document.addEventListener('click', function(e) {
        // Check if the click was on an input with a URL
        if (e.target.tagName === 'INPUT' && e.target.type === 'text' && e.target.value.startsWith('http')) {
            // Only trigger for URL inputs like the one in the screenshot
            const urlBar = document.querySelector('.url-bar');
            if (e.target === urlBar) {
                e.preventDefault();
                openFeedPopup(e.target.value);
            }
        }
    });
});
