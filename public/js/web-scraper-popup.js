/**
 * Web Scraper Popup - CSS Selector Tool
 *
 * Provides a popup interface for selecting elements on a webpage
 * and extracting CSS selectors for RSS feeds.
 */

class WebScraperPopup {
    constructor(options = {}) {
        this.options = {
            targetOrigin: window.location.origin,
            ...options
        };

        this.elements = {};
        this.state = {
            selector: 'article',
            selectedElements: [],
            mode: 'auto',
            contentType: 'news',
            titleSelector: '',
            matchingEntries: []
        };

        this.init();
    }

    init() {
        this.createPopup();
        this.attachEventListeners();
        this.updateSelectedElements();
    }

    createPopup() {
        // Create popup container
        const popupContainer = document.createElement('div');
        popupContainer.id = 'web-scraper-popup';
        popupContainer.className = 'web-scraper-container';

        // Create popup content
        popupContainer.innerHTML = `
            <div class="web-scraper-header">
                <button class="web-scraper-back-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <div class="web-scraper-url-container">
                    <input type="text" class="web-scraper-url" value="${window.location.href}" readonly />
                </div>
                <div class="web-scraper-js-toggle">
                    <input type="checkbox" id="render-js-checkbox" checked />
                    <label for="render-js-checkbox">Render JavaScript</label>
                </div>
            </div>

            <div class="web-scraper-content">
                <div class="web-scraper-left">
                    <div class="web-scraper-website" id="website-preview">
                        <div class="web-scraper-loading" id="preview-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="web-scraper-preview-content" id="preview-content">
                            <!-- Website content will be cloned here -->
                        </div>
                    </div>
                </div>

                <div class="web-scraper-right">
                    <div class="web-scraper-panel">
                        <div class="web-scraper-panel-header">
                            <h4>Type of Content</h4>
                        </div>
                        <div class="web-scraper-panel-body">
                            <div class="web-scraper-content-type">
                                <div class="web-scraper-select">
                                    <div class="web-scraper-selected">
                                        <i class="fas fa-newspaper"></i> News
                                    </div>
                                    <div class="web-scraper-dropdown">
                                        <div class="web-scraper-option" data-value="news">
                                            <i class="fas fa-newspaper"></i> News
                                        </div>
                                        <div class="web-scraper-option" data-value="blog">
                                            <i class="fas fa-blog"></i> Blog
                                        </div>
                                        <div class="web-scraper-option" data-value="videos">
                                            <i class="fas fa-video"></i> Videos
                                        </div>
                                        <div class="web-scraper-option" data-value="products">
                                            <i class="fas fa-shopping-cart"></i> Products
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="web-scraper-panel">
                        <div class="web-scraper-panel-header">
                            <h4>Mode</h4>
                        </div>
                        <div class="web-scraper-panel-body">
                            <div class="web-scraper-mode">
                                <div class="web-scraper-radio-group">
                                    <label class="web-scraper-radio-label">
                                        <input type="radio" name="mode" value="auto" checked />
                                        <span class="web-scraper-radio-text">Auto</span>
                                    </label>
                                    <label class="web-scraper-radio-label">
                                        <input type="radio" name="mode" value="manual" />
                                        <span class="web-scraper-radio-text">Manual</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="web-scraper-panel">
                        <div class="web-scraper-panel-header">
                            <h4>CSS Selector <span class="required">*</span></h4>
                        </div>
                        <div class="web-scraper-panel-body">
                            <div class="web-scraper-selector">
                                <input type="text" id="css-selector" value="article" placeholder="Example: article, .news-item, #content div.post" />
                            </div>
                            <div class="web-scraper-selector-examples">
                                <div class="web-scraper-selector-example" data-selector="article">
                                    <h6>Bài viết</h6>
                                    <code>article</code>
                                </div>
                                <div class="web-scraper-selector-example" data-selector=".news-item">
                                    <h6>Class news-item</h6>
                                    <code>.news-item</code>
                                </div>
                                <div class="web-scraper-selector-example" data-selector="#content .post">
                                    <h6>Posts trong content</h6>
                                    <code>#content .post</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="web-scraper-panel">
                        <div class="web-scraper-panel-header">
                            <h4>Title CSS Selector <span class="required">*</span></h4>
                        </div>
                        <div class="web-scraper-panel-body">
                            <div class="web-scraper-selector">
                                <input type="text" id="title-selector" value="div > div > div > div > div > div > div > div > div" />
                            </div>
                        </div>
                    </div>

                    <div class="web-scraper-panel">
                        <div class="web-scraper-panel-header">
                            <h4>Matching entries <i class="fas fa-chevron-down"></i></h4>
                        </div>
                        <div class="web-scraper-panel-body">
                            <div class="web-scraper-matching-entries" id="matching-entries">
                                <div class="web-scraper-loading-entries">
                                    <i class="fas fa-spin fa-spinner"></i> Loading matching entries...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="web-scraper-footer">
                <div class="web-scraper-feed-options">
                    <div class="web-scraper-feed-title">
                        <label for="feed-title">Feed Title</label>
                        <input type="text" id="feed-title" placeholder="Enter feed title" />
                    </div>
                </div>

                <div class="web-scraper-actions">
                    <button class="web-scraper-btn web-scraper-generate-btn">
                        Generate
                    </button>
                    <div class="web-scraper-selected-count">
                        Selected <span id="selected-count">25</span> elements
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(popupContainer);

        // Save references to DOM elements
        this.elements.popup = popupContainer;
        this.elements.backBtn = popupContainer.querySelector('.web-scraper-back-btn');
        this.elements.urlInput = popupContainer.querySelector('.web-scraper-url');
        this.elements.renderJsCheckbox = popupContainer.querySelector('#render-js-checkbox');
        this.elements.cssSelector = popupContainer.querySelector('#css-selector');
        this.elements.titleSelector = popupContainer.querySelector('#title-selector');
        this.elements.previewContent = popupContainer.querySelector('#preview-content');
        this.elements.modeRadios = popupContainer.querySelectorAll('input[name="mode"]');
        this.elements.matchingEntries = popupContainer.querySelector('#matching-entries');
        this.elements.selectorExamples = popupContainer.querySelectorAll('.web-scraper-selector-example');
        this.elements.generateBtn = popupContainer.querySelector('.web-scraper-generate-btn');
        this.elements.selectedCount = popupContainer.querySelector('#selected-count');
        this.elements.feedTitle = popupContainer.querySelector('#feed-title');

        // Clone current page content into preview
        this.clonePageContent();
    }

    clonePageContent() {
        // Clone the body content without scripts
        const bodyClone = document.body.cloneNode(true);

        // Remove the popup from the clone
        const popupInClone = bodyClone.querySelector('#web-scraper-popup');
        if (popupInClone) {
            bodyClone.removeChild(popupInClone);
        }

        // Remove all scripts
        const scripts = bodyClone.querySelectorAll('script');
        scripts.forEach(script => script.remove());

        // Copy the clone to the preview
        this.elements.previewContent.innerHTML = '';
        Array.from(bodyClone.children).forEach(child => {
            this.elements.previewContent.appendChild(child.cloneNode(true));
        });

        // Add mouseover highlighting for elements
        this.addElementHighlighting(this.elements.previewContent);
    }

    addElementHighlighting(container) {
        // Add hover highlighting to elements
        const allElements = container.querySelectorAll('*');
        allElements.forEach(el => {
            el.addEventListener('mouseover', (e) => {
                e.stopPropagation();
                const selector = this.state.selector;
                if (container.querySelector(selector) && !el.matches(selector)) {
                    return; // Only highlight if we're in selector matching mode
                }
                el.classList.add('web-scraper-hover-highlight');
            });

            el.addEventListener('mouseout', () => {
                el.classList.remove('web-scraper-hover-highlight');
            });

            el.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Generate a CSS selector for this element
                const uniqueSelector = this.generateUniqueSelector(el);
                this.elements.cssSelector.value = uniqueSelector;
                this.state.selector = uniqueSelector;

                this.updateSelectedElements();
            });
        });
    }

    generateUniqueSelector(element) {
        // Simple function to generate a CSS selector for an element
        if (element.id) {
            return `#${element.id}`;
        }

        if (element.className && typeof element.className === 'string') {
            const classes = element.className.split(' ').filter(c => c.trim() !== '');
            if (classes.length > 0) {
                return `.${classes.join('.')}`;
            }
        }

        // Try to create a selector with the element's tag and position
        let selector = element.tagName.toLowerCase();

        // Add parent tags for more specificity
        let parent = element.parentElement;
        let depth = 0;
        const maxDepth = 3;

        while (parent && parent !== document.body && depth < maxDepth) {
            const tag = parent.tagName.toLowerCase();
            selector = `${tag} > ${selector}`;
            parent = parent.parentElement;
            depth++;
        }

        return selector;
    }

    updateSelectedElements() {
        try {
            const selector = this.state.selector;
            if (!selector) return;

            // Clear previous highlights
            const previouslyHighlighted = this.elements.previewContent.querySelectorAll('.web-scraper-selected-highlight');
            previouslyHighlighted.forEach(el => {
                el.classList.remove('web-scraper-selected-highlight');
            });

            // Find and highlight matching elements
            const matchingElements = this.elements.previewContent.querySelectorAll(selector);
            this.state.selectedElements = Array.from(matchingElements);

            // Update count
            this.elements.selectedCount.textContent = this.state.selectedElements.length;

            // Highlight elements
            this.state.selectedElements.forEach(el => {
                el.classList.add('web-scraper-selected-highlight');
            });

            // Extract and display matching entries
            this.updateMatchingEntries();
        } catch (e) {
            console.error('Error updating selected elements:', e);
        }
    }

    updateMatchingEntries() {
        if (this.state.selectedElements.length === 0) {
            this.elements.matchingEntries.innerHTML = '<div class="web-scraper-no-matches">No matching elements found</div>';
            return;
        }

        let entriesHtml = '';

        // Extract data from selected elements
        this.state.matchingEntries = this.state.selectedElements.map((el, index) => {
            // Extract title
            let title = '';
            const headings = el.querySelectorAll('h1, h2, h3, h4, h5');
            if (headings.length > 0) {
                title = headings[0].textContent.trim();
            } else if (el.querySelector('a')) {
                title = el.querySelector('a').textContent.trim();
            } else {
                title = `Item ${index + 1}`;
            }

            // Extract URL
            let url = '';
            if (el.querySelector('a')) {
                url = el.querySelector('a').href;
            }

            return { title, url, element: el };
        });

        // Generate HTML for matching entries
        this.state.matchingEntries.forEach((entry, index) => {
            entriesHtml += `
                <div class="web-scraper-entry" data-index="${index}">
                    <div class="web-scraper-entry-title">${entry.title}</div>
                    ${entry.url ? `<div class="web-scraper-entry-url">${entry.url}</div>` : ''}
                </div>
            `;
        });

        // Update matching entries container
        this.elements.matchingEntries.innerHTML = entriesHtml;

        // Add click event listeners to entries
        const entryElements = this.elements.matchingEntries.querySelectorAll('.web-scraper-entry');
        entryElements.forEach(entry => {
            entry.addEventListener('click', () => {
                // Remove selected class from all entries
                entryElements.forEach(e => e.classList.remove('web-scraper-selected-entry'));

                // Add selected class to clicked entry
                entry.classList.add('web-scraper-selected-entry');

                // Scroll to the corresponding element
                const index = parseInt(entry.dataset.index);
                if (this.state.matchingEntries[index]) {
                    const element = this.state.matchingEntries[index].element;
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    }

    attachEventListeners() {
        // CSS selector input change
        this.elements.cssSelector.addEventListener('input', () => {
            this.state.selector = this.elements.cssSelector.value;
            this.updateSelectedElements();
        });

        // Title selector input change
        this.elements.titleSelector.addEventListener('input', () => {
            this.state.titleSelector = this.elements.titleSelector.value;
        });

        // Mode radio buttons
        this.elements.modeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                this.state.mode = radio.value;
            });
        });

        // Selector examples
        this.elements.selectorExamples.forEach(example => {
            example.addEventListener('click', () => {
                const selector = example.dataset.selector;
                this.elements.cssSelector.value = selector;
                this.state.selector = selector;
                this.updateSelectedElements();
            });
        });

        // Back button
        this.elements.backBtn.addEventListener('click', () => {
            // In a real implementation, this would navigate back
            alert('Back button clicked');
        });

        // Generate button
        this.elements.generateBtn.addEventListener('click', () => {
            // In a real implementation, this would submit the form
            alert(`Generating RSS feed with selector: ${this.state.selector}\nFound: ${this.state.selectedElements.length} elements`);
        });
    }
}

// Initialize the popup when script is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        /* Web Scraper Popup Styles */
        .web-scraper-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #fff;
            display: flex;
            flex-direction: column;
            z-index: 9999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .web-scraper-header {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            background: #f5f5f5;
        }

        .web-scraper-back-btn {
            background: none;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            color: #333;
            font-size: 14px;
        }

        .web-scraper-url-container {
            flex: 1;
            margin: 0 10px;
        }

        .web-scraper-url {
            width: 100%;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background: white;
        }

        .web-scraper-js-toggle {
            margin-left: 10px;
            font-size: 14px;
        }

        .web-scraper-content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .web-scraper-left {
            flex: 7;
            overflow: hidden;
            position: relative;
            border-right: 1px solid #ddd;
        }

        .web-scraper-website {
            height: 100%;
            position: relative;
        }

        .web-scraper-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.8);
            z-index: 2;
        }

        .web-scraper-preview-content {
            height: 100%;
            width: 100%;
            overflow: auto;
            position: relative;
        }

        .web-scraper-right {
            flex: 3;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
        }

        .web-scraper-panel {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            overflow: hidden;
        }

        .web-scraper-panel-header {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .web-scraper-panel-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
        }

        .web-scraper-panel-body {
            padding: 15px;
        }

        .web-scraper-selector input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            margin-bottom: 10px;
        }

        .web-scraper-selector-examples {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .web-scraper-selector-example {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.2s;
            background-color: #f9f9f9;
        }

        .web-scraper-selector-example:hover {
            border-color: #4a6cf7;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .web-scraper-selector-example h6 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 14px;
        }

        .web-scraper-selector-example code {
            display: block;
            padding: 5px;
            background: #f0f0f0;
            border-radius: 3px;
            color: #d63384;
            font-family: monospace;
        }

        .web-scraper-matching-entries {
            max-height: 300px;
            overflow-y: auto;
        }

        .web-scraper-entry {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .web-scraper-entry:hover {
            background-color: #f8f9fa;
        }

        .web-scraper-entry-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .web-scraper-entry-url {
            font-size: 12px;
            color: #4a6cf7;
            word-break: break-all;
        }

        .web-scraper-selected-entry {
            background-color: rgba(74, 108, 247, 0.1);
            border-left: 3px solid #4a6cf7;
        }

        .web-scraper-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-top: 1px solid #ddd;
            background: white;
        }

        .web-scraper-feed-title {
            display: flex;
            flex-direction: column;
        }

        .web-scraper-feed-title label {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .web-scraper-feed-title input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }

        .web-scraper-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .web-scraper-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }

        .web-scraper-generate-btn {
            background-color: #ff7846;
            color: white;
        }

        .web-scraper-selected-count {
            color: #666;
            font-size: 14px;
        }

        .web-scraper-hover-highlight {
            outline: 2px dashed #ff7846 !important;
            background-color: rgba(255, 120, 70, 0.1) !important;
        }

        .web-scraper-selected-highlight {
            outline: 2px solid #ff7846 !important;
            background-color: rgba(255, 120, 70, 0.2) !important;
        }

        .required {
            color: #dc3545;
        }

        .web-scraper-content-type {
            position: relative;
        }

        .web-scraper-select {
            position: relative;
        }

        .web-scraper-selected {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            background: white;
        }

        .web-scraper-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
            z-index: 10;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: none;
        }

        .web-scraper-select:hover .web-scraper-dropdown {
            display: block;
        }

        .web-scraper-option {
            padding: 8px 12px;
            cursor: pointer;
        }

        .web-scraper-option:hover {
            background-color: #f8f9fa;
        }

        .web-scraper-radio-group {
            display: flex;
            gap: 20px;
        }

        .web-scraper-radio-label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .spinner-border {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }

        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    `;
    document.head.appendChild(style);

    // Initialize the popup
    const popup = new WebScraperPopup();

    // For testing, expose the popup instance to global scope
    window.webScraperPopup = popup;
});
