/**
 * RSS Feed Generator - Element Selector
 * Handles the visual selection of elements and generates CSS selectors
 */

document.addEventListener('DOMContentLoaded', () => {
    // DOM References
    const iframe = document.getElementById('web-preview');
    const loadingOverlay = document.getElementById('loading-overlay');
    const cssSelector = document.getElementById('css-selector');
    const selectorText = document.getElementById('selector-text');
    const matchingEntries = document.getElementById('matching-entries');
    const selectedCount = document.getElementById('selected-count');
    const itemsCount = document.getElementById('items-count');
    const generateBtn = document.getElementById('generate-btn');
    const backButton = document.getElementById('back-button');
    const refreshPreview = document.getElementById('refresh-preview');
    const jsToggle = document.getElementById('js-toggle-checkbox');
    const modalElement = document.getElementById('feed-modal');
    const modalCloseBtn = document.getElementById('modal-close');
    const cancelBtn = document.getElementById('cancel-btn');
    const submitFeedBtn = document.getElementById('submit-feed');
    const feedForm = document.getElementById('feed-form');

    // State variables
    let selectedElements = [];
    let currentSelector = '';
    let hoveredElement = null;
    let selectedElement = null;
    let iframeDocument = null;
    let mapData = null; // Store mapping data for selected elements

    // Track if mouse button is being held down for drag select
    let isMouseDown = false;

    // Initialize the iframe when document is ready
    initializeIframe();

    // Initialize event listeners for UI elements
    initializeUIEvents();

    /**
     * Initialize the iframe with the target URL
     */
    function initializeIframe() {
        // Get URL from the input field
        const url = document.getElementById('url-input').value;

        if (!url) {
            alert('URL is required to continue');
            return;
        }

        // Show loading overlay
        loadingOverlay.style.display = 'flex';

        // Set iframe source with proxy to avoid CORS issues
        const renderJs = jsToggle.checked ? '1' : '0';
        const proxyUrl = `/web-scraper/proxy?url=${encodeURIComponent(url)}&render_js=${renderJs}`;

        // Add load event handler to iframe
        iframe.onload = function() {
            try {
                iframeDocument = iframe.contentDocument || iframe.contentWindow.document;

                // Add styles for highlighting elements
                addHighlightStyles();

                // Setup interactions with iframe content
                setupIframeInteractions();

                // Hide loading overlay
                loadingOverlay.style.display = 'none';
            } catch (e) {
                console.error('Error setting up iframe:', e);
                alert('Error loading website. Please try another URL.');
                loadingOverlay.style.display = 'none';
            }
        };

        // Set iframe source
        iframe.src = proxyUrl;
    }

    /**
     * Add highlight styles to iframe document
     */
    function addHighlightStyles() {
        if (!iframeDocument) return;

        const style = iframeDocument.createElement('style');
        style.textContent = `
            .highlight-hover {
                outline: 2px dashed #ff7846 !important;
                background-color: rgba(255, 120, 70, 0.1) !important;
                cursor: pointer !important;
            }
            .highlight-selected {
                outline: 3px solid #ff7846 !important;
                background-color: rgba(255, 120, 70, 0.2) !important;
            }
            .highlight-similar {
                outline: 2px solid #ff7846 !important;
                background-color: rgba(255, 120, 70, 0.1) !important;
            }
            * {
                cursor: pointer !important;
            }
        `;
        iframeDocument.head.appendChild(style);
        
        // Add styles for matching entries
        const mainStyle = document.createElement('style');
        mainStyle.textContent = `
            .entry-item {
                padding: 12px;
                border-bottom: 1px solid #eee;
                transition: all 0.2s;
                margin-bottom: 10px;
                border-radius: 6px;
            }
            
            .entry-item:hover {
                background-color: #f9f9f9;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            
            .entry-title {
                font-weight: 600;
                font-size: 16px;
                margin-bottom: 8px;
                color: #333;
                line-height: 1.4;
            }
            
            .entry-title a {
                color: #0066cc;
                text-decoration: none;
                transition: color 0.2s;
            }
            
            .entry-title a:hover {
                color: #ff7846;
                text-decoration: underline;
            }
            
            .entry-selector {
                display: flex;
                align-items: flex-start;
                margin-bottom: 10px;
                font-size: 12px;
                color: #666;
                background-color: #f5f5f5;
                padding: 8px;
                border-radius: 4px;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .entry-selector i {
                margin-right: 5px;
                min-width: 16px;
                color: #888;
            }
            
            .selector-text {
                font-family: monospace;
                color: #444;
            }
            
            .entry-url-wrapper, .entry-description-wrapper, .entry-date-wrapper {
                display: flex;
                align-items: flex-start;
                margin-bottom: 6px;
                font-size: 13px;
                color: #666;
            }
            
            .entry-url-wrapper i, .entry-description-wrapper i, .entry-date-wrapper i {
                margin-right: 5px;
                min-width: 16px;
                color: #888;
            }
            
            .entry-url {
                color: #0066cc;
                word-break: break-all;
                text-decoration: none;
                font-size: 12px;
            }
            
            .entry-url:hover {
                text-decoration: underline;
            }
            
            .entry-description {
                line-height: 1.5;
                color: #555;
            }
            
            .entry-image-wrapper {
                margin: 8px 0;
                max-height: 150px;
                overflow: hidden;
                border-radius: 4px;
            }
            
            .entry-image {
                max-width: 100%;
                height: auto;
                display: block;
                border-radius: 4px;
                transition: transform 0.3s;
            }
            
            .entry-image:hover {
                transform: scale(1.02);
            }
            
            .entry-date-wrapper {
                font-size: 12px;
                color: #888;
            }
        `;
        document.head.appendChild(mainStyle);
    }

    /**
     * Initialize event listeners for UI elements
     */
    function initializeUIEvents() {
        // Back button
        if (backButton) {
            backButton.addEventListener('click', () => {
                window.location.href = '/web-scraper';
            });
        }

        // Generate button
        if (generateBtn) {
            generateBtn.addEventListener('click', () => {
                if (selectedElements.length === 0) {
                    alert('Vui lòng chọn phần tử trên trang trước khi tạo RSS feed.');
                    return;
                }

                openFeedModal();
            });
        }

        // Refresh preview button
        if (refreshPreview) {
            refreshPreview.addEventListener('click', () => {
                if (currentSelector) {
                    applySelector(currentSelector);
                }
            });
        }

        // Reset selection button
        const resetBtn = document.getElementById('reset-selection');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                resetSelection();
            });
        }

        // JS Toggle
        if (jsToggle) {
            jsToggle.addEventListener('change', () => {
                initializeIframe();
            });
        }

        // Modal close button
        if (modalCloseBtn) {
            modalCloseBtn.addEventListener('click', () => {
                closeFeedModal();
            });
        }

        // Cancel button in modal
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                closeFeedModal();
            });
        }

        // Submit feed button
        if (submitFeedBtn) {
            submitFeedBtn.addEventListener('click', () => {
                // Set the selector and URL values before submitting
                document.getElementById('form-selector').value = currentSelector;
                document.getElementById('form-url').value = document.getElementById('url-input').value;

                // Submit the form
                feedForm.submit();
            });
        }

        // Content type selection
        const modeOptions = document.querySelectorAll('.mode-option');
        if (modeOptions.length > 0) {
            modeOptions.forEach(option => {
                option.addEventListener('click', () => {
                    // Remove active class from all options
                    modeOptions.forEach(o => o.classList.remove('active'));
                    // Add active class to clicked option
                    option.classList.add('active');
                    // Update hidden input
                    document.getElementById('content-type').value = option.dataset.value;
                });
            });
        }

        // Example selectors
        const exampleItems = document.querySelectorAll('.example-item');
        if (exampleItems.length > 0) {
            exampleItems.forEach(item => {
                item.addEventListener('click', () => {
                    const selector = item.dataset.selector;
                    if (selector) {
                        resetSelection(); // Reset current selection first
                        applySelector(selector);
                    }
                });
            });
        }
    }

    /**
     * Reset the current selection
     */
    function resetSelection() {
        console.log("Đang reset toàn bộ lựa chọn...");

        // Clear selection variables
        selectedElement = null;
        selectedElements = [];
        currentSelector = '';
        mapData = null;

        // Remove all highlights before any other operations
        removeAllHighlights();

        // Clear UI elements
        if (cssSelector) cssSelector.value = '';
        if (selectorText) selectorText.textContent = 'Click vào phần tử để tạo selector mới';

        // Clear counts
        if (selectedCount) selectedCount.textContent = '0';
        if (itemsCount) itemsCount.textContent = '(0)';

        // Clear matching entries
        if (matchingEntries) {
            matchingEntries.innerHTML = '<div class="entry-item"><div class="entry-title">Đã xóa lựa chọn. Click vào phần tử bất kỳ để chọn lại.</div></div>';
        }

        // Clear mapping
        const mappingContainer = document.getElementById('selector-mapping');
        if (mappingContainer) {
            mappingContainer.innerHTML = '<div class="mapping-item">Chọn một phần tử để xem mapping data</div>';
        }

        console.log("Đã reset toàn bộ lựa chọn, sẵn sàng chọn lại.");
    }

    /**
     * Open feed generation modal
     */
    function openFeedModal() {
        if (!modalElement) return;

        // Set default title based on URL
        const urlInput = document.getElementById('url-input');
        const feedTitle = document.getElementById('feed-title');

        if (urlInput && feedTitle) {
            const domain = new URL(urlInput.value).hostname;
            feedTitle.value = `RSS Feed - ${domain}`;
        }

        // Show modal
        modalElement.style.display = 'flex';
    }

    /**
     * Close feed generation modal
     */
    function closeFeedModal() {
        if (!modalElement) return;
        modalElement.style.display = 'none';
    }

    /**
     * Setup interactions with iframe content
     */
    function setupIframeInteractions() {
        if (!iframeDocument) return;

        // Remove existing event listeners to avoid duplicates
        iframeDocument.removeEventListener('mouseover', handleMouseOver);
        iframeDocument.removeEventListener('mouseout', handleMouseOut);
        iframeDocument.removeEventListener('click', handleClick);

        // Add event listeners
        iframeDocument.addEventListener('mouseover', handleMouseOver);
        iframeDocument.addEventListener('mouseout', handleMouseOut);
        iframeDocument.addEventListener('click', handleClick);

        // Mouse down/up tracking for potential drag selection
        iframeDocument.addEventListener('mousedown', () => { isMouseDown = true; });
        iframeDocument.addEventListener('mouseup', () => { isMouseDown = false; });

        // Try to find elements automatically
        setTimeout(autoDetectElements, 1000);
    }

    /**
     * Auto detect important elements on the page
     */
    function autoDetectElements() {
        // Danh sách các bộ chọn phổ biến cho các trang tin tức/blog
        const commonSelectors = [
            // Bài viết hoàn chỉnh hoặc khối bài viết
            'article', '.article', '.item', '.post', '.news-item', '.entry', 
            '.card', '.list-item', '.story', '.teaser',
            
            // Container chứa nhiều bài viết
            '.news-list', '.article-list', '.list-news', '.list-articles',
            '.article-container', '.item-list', '.feed-list', '.stories',
            
            // Container phân chia rõ ràng
            '.row > div', '.grid > div', '.col > div', 'section > div',
            
            // Các container thường chứa nhiều bài viết tương tự nhau
            'div.row div[class*="col"]', 'div.grid div[class*="item"]',
            '.listing div[class*="item"]', '.content div[class*="article"]',

            // Container tiêu đề có thể là dấu hiệu tốt
            'h2 a', 'h3 a', '.title a', '.heading a', '.item-title a'
        ];

        // Tìm selector hiệu quả nhất
        let bestSelector = '';
        let maxMatches = 3; // Cần ít nhất 3 phần tử để xác định một mẫu

        for (const selector of commonSelectors) {
            try {
                const elements = iframeDocument.querySelectorAll(selector);
                // Nếu tìm thấy nhiều phần tử và nhiều hơn kết quả tốt nhất trước đó
                if (elements.length > maxMatches) {
                    // Kiểm tra các phần tử có đủ tương đồng không
                    if (areElementsSimilar(elements)) {
                        maxMatches = elements.length;
                        bestSelector = selector;
                        console.log(`Tìm thấy ${elements.length} phần tử phù hợp với selector: ${selector}`);
                    }
                }
            } catch (e) {
                console.warn(`Không thể truy vấn selector: ${selector}`, e);
            }
        }

        // Nếu tìm thấy selector phù hợp
        if (bestSelector) {
            console.log(`Áp dụng selector tốt nhất: ${bestSelector} với ${maxMatches} phần tử`);
            applySelector(bestSelector);
        } else {
            // Nếu không tìm thấy selector phù hợp, thử phương pháp khác
            findNewsItemsByContent();
        }
    }

    /**
     * Kiểm tra xem các phần tử có tương đồng không
     */
    function areElementsSimilar(elements) {
        if (elements.length < 3) return false;
        
        // Lấy mẫu để so sánh
        const sample = Array.from(elements).slice(0, Math.min(5, elements.length));
        
        // Kiểm tra các đặc điểm cơ bản
        const heights = sample.map(el => el.offsetHeight);
        const avgHeight = heights.reduce((sum, h) => sum + h, 0) / heights.length;
        
        // Kiểm tra nếu ít nhất 60% phần tử có chiều cao tương tự
        const similarHeightCount = heights.filter(h => Math.abs(h - avgHeight) < avgHeight * 0.4).length;
        const heightSimilarity = similarHeightCount / heights.length;
        
        // Kiểm tra nếu phần lớn phần tử có thẻ con giống nhau
        const tagCounts = sample.map(el => {
            const tags = {};
            el.querySelectorAll('*').forEach(child => {
                const tag = child.tagName.toLowerCase();
                tags[tag] = (tags[tag] || 0) + 1;
            });
            return tags;
        });
        
        // Kiểm tra nếu hầu hết các phần tử đều chứa thẻ a (link)
        const hasLinks = sample.filter(el => el.querySelector('a')).length;
        const linkSimilarity = hasLinks / sample.length;
        
        // Kiểm tra nếu nhiều phần tử chứa hình ảnh
        const hasImages = sample.filter(el => el.querySelector('img')).length;
        const imageSimilarity = hasImages / sample.length;
        
        // Trả về true nếu các phần tử đủ tương đồng
        return (heightSimilarity > 0.6 && linkSimilarity > 0.7) || 
               (linkSimilarity > 0.8 && imageSimilarity > 0.5);
    }

    /**
     * Tìm các phần tử tin tức dựa trên nội dung
     */
    function findNewsItemsByContent() {
        console.log("Tìm tin tức dựa trên phân tích nội dung...");
        
        // Tìm tất cả các phần tử có chứa liên kết
        const allElements = iframeDocument.querySelectorAll('div, section, li, article');
        const candidates = [];
        
        // Phân tích từng phần tử
        allElements.forEach(el => {
            // Bỏ qua các phần tử quá nhỏ hoặc không hiển thị
            if (el.offsetWidth < 50 || el.offsetHeight < 50) return;
            if (getComputedStyle(el).display === 'none') return;
            
            // Kiểm tra xem phần tử có chứa các thành phần của tin tức không
            const hasLink = el.querySelectorAll('a').length > 0;
            const hasHeading = el.querySelectorAll('h1, h2, h3, h4, h5, h6').length > 0;
            const hasImage = el.querySelectorAll('img').length > 0;
            const hasText = el.textContent.trim().length > 20;
            
            // Tính điểm cho phần tử dựa trên các đặc điểm
            let score = 0;
            if (hasLink) score += 2;
            if (hasHeading) score += 3;
            if (hasImage) score += 2;
            if (hasText) score += 1;
            
            // Kiểm tra kích thước hợp lý cho một bài báo
            const area = el.offsetWidth * el.offsetHeight;
            if (area > 10000 && area < 300000) score += 2;
            
            // Lưu ứng viên và điểm
            if (score >= 5) {
                candidates.push({
                    element: el,
                    score: score
                });
            }
        });
        
        // Sắp xếp theo điểm cao nhất
        candidates.sort((a, b) => b.score - a.score);
        
        // Phân nhóm các ứng viên theo kích thước và vị trí
        const groups = groupSimilarElements(candidates.map(c => c.element));
        
        // Tìm nhóm lớn nhất
        let largestGroup = [];
        for (const group of groups) {
            if (group.length > largestGroup.length && group.length >= 3) {
                largestGroup = group;
            }
        }
        
        if (largestGroup.length >= 3) {
            // Tìm selector chung cho nhóm
            const commonSelector = findCommonSelector(largestGroup);
            console.log(`Tìm thấy nhóm ${largestGroup.length} phần tử với selector: ${commonSelector}`);
            applySelector(commonSelector);
        } else {
            console.log("Không tìm thấy nhóm tin tức phù hợp. Sử dụng phương pháp dự phòng...");
            // Nếu không tìm thấy nhóm, sử dụng phần tử đầu tiên có điểm cao
            if (candidates.length > 0) {
                const bestCandidate = candidates[0].element;
                const selector = generateSimpleSelector(bestCandidate);
                applySelector(selector);
            }
        }
    }

    /**
     * Nhóm các phần tử tương tự nhau
     */
    function groupSimilarElements(elements) {
        const groups = [];
        const processed = new Set();
        
        for (let i = 0; i < elements.length; i++) {
            if (processed.has(i)) continue;
            
            const current = elements[i];
            const group = [current];
            processed.add(i);
            
            // Tìm các phần tử tương tự
            for (let j = i + 1; j < elements.length; j++) {
                if (processed.has(j)) continue;
                
                const other = elements[j];
                
                // So sánh khung và cấu trúc
                if (areElementsStructurallySimilar(current, other)) {
                    group.push(other);
                    processed.add(j);
                }
            }
            
            if (group.length > 1) {
                groups.push(group);
            }
        }
        
        return groups;
    }

    /**
     * Kiểm tra xem hai phần tử có cấu trúc tương tự không
     */
    function areElementsStructurallySimilar(el1, el2) {
        // So sánh theo kích thước
        const h1 = el1.offsetHeight;
        const h2 = el2.offsetHeight;
        const w1 = el1.offsetWidth;
        const w2 = el2.offsetWidth;
        
        const sizeSimilar = Math.abs(h1 - h2) < Math.max(h1, h2) * 0.5 &&
                             Math.abs(w1 - w2) < Math.max(w1, w2) * 0.5;
        
        if (!sizeSimilar) return false;
        
        // So sánh cấu trúc DOM
        const tags1 = Array.from(el1.querySelectorAll('*')).map(e => e.tagName);
        const tags2 = Array.from(el2.querySelectorAll('*')).map(e => e.tagName);
        
        // Tìm các thẻ phổ biến
        const commonTags = tags1.filter(tag => tags2.includes(tag));
        
        // Tính độ tương đồng
        const similarity = commonTags.length / Math.max(tags1.length, tags2.length);
        
        return similarity > 0.6;
    }

    /**
     * Tìm selector chung cho một tập hợp các phần tử
     */
    function findCommonSelector(elements) {
        if (elements.length === 0) return '';
        
        // Thử với hàm generateSelector đã có
        const firstSelector = generateSelector(elements[0]);
        
        // Kiểm tra xem selector có phù hợp với tất cả các phần tử không
        try {
            const matches = iframeDocument.querySelectorAll(firstSelector);
            if (matches.length >= elements.length) {
                // Kiểm tra xem tất cả các phần tử đều được chọn
                const allSelected = elements.every(el => {
                    return Array.from(matches).some(match => match === el);
                });
                
                if (allSelected) return firstSelector;
            }
        } catch (e) {
            console.warn("Selector không hợp lệ:", firstSelector);
        }
        
        // Thử tìm selector chung dựa trên các thuộc tính
        for (const element of elements) {
            // Thử với class
            if (element.className) {
                const classes = element.className.split(' ')
                    .filter(cls => cls && !cls.includes('highlight-'));
                
                for (const cls of classes) {
                    const selector = `.${cls}`;
                    try {
                        const matches = iframeDocument.querySelectorAll(selector);
                        if (matches.length >= elements.length) {
                            const coverage = elements.filter(el => 
                                Array.from(matches).some(match => match === el || match.contains(el))
                            ).length / elements.length;
                            
                            if (coverage > 0.8) return selector;
                        }
                    } catch (e) {}
                }
            }
            
            // Thử với thẻ và vị trí
            const tagName = element.tagName.toLowerCase();
            const parent = element.parentElement;
            
            if (parent) {
                if (parent.className) {
                    const parentClasses = parent.className.split(' ')
                        .filter(cls => cls && !cls.includes('highlight-'));
                    
                    for (const cls of parentClasses) {
                        const selector = `.${cls} > ${tagName}`;
                        try {
                            const matches = iframeDocument.querySelectorAll(selector);
                            if (matches.length >= elements.length) {
                                const coverage = elements.filter(el => 
                                    Array.from(matches).some(match => match === el)
                                ).length / elements.length;
                                
                                if (coverage > 0.8) return selector;
                            }
                        } catch (e) {}
                    }
                }
            }
        }
        
        // Dự phòng: sử dụng selector đơn giản dựa trên thẻ phổ biến nhất
        const tagCounts = {};
        elements.forEach(el => {
            const tag = el.tagName.toLowerCase();
            tagCounts[tag] = (tagCounts[tag] || 0) + 1;
        });
        
        let mostCommonTag = 'div';
        let maxCount = 0;
        for (const [tag, count] of Object.entries(tagCounts)) {
            if (count > maxCount) {
                maxCount = count;
                mostCommonTag = tag;
            }
        }
        
        return mostCommonTag;
    }

    /**
     * Handle mouse over events in iframe
     */
    function handleMouseOver(e) {
        // Skip if we already have a selection
        if (isMouseDown) return;

        const element = e.target;

        // Skip body and html elements
        if (element.tagName === 'BODY' || element.tagName === 'HTML') return;

        // Add hover highlight
        element.classList.add('highlight-hover');
        hoveredElement = element;

        // Show potential selector in a tooltip or status area
        const selector = generateSelector(element);
        showSelectorPreview(selector);
    }

    /**
     * Handle mouse out events in iframe
     */
    function handleMouseOut(e) {
        const element = e.target;

        // Remove hover highlight
        element.classList.remove('highlight-hover');

        if (hoveredElement === element) {
            hoveredElement = null;
        }
    }

    /**
     * Handle click events in iframe
     */
    function handleClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const element = e.target;

        // Skip body and html elements
        if (element.tagName === 'BODY' || element.tagName === 'HTML') return;

        // Xóa highlight cũ trước khi chọn phần tử mới
        removeAllHighlights();

        // Reset các biến để chọn mới hoàn toàn
        selectedElement = null;
        selectedElements = [];
        currentSelector = '';
        mapData = null;

        console.log("Đã click vào phần tử:", element.tagName, 
                    element.className ? element.className : "(no class)",
                    element.id ? element.id : "(no id)");

        try {
            // Bước 1: Tìm container bài viết tốt nhất từ phần tử được click
            const bestContainer = findBestNewsContainer(element);
            
            if (bestContainer) {
                console.log("Tìm thấy container bài viết:", bestContainer.tagName,
                            bestContainer.className ? bestContainer.className : "(no class)");
                            
                // Lưu container đã chọn
                selectedElement = bestContainer;
                
                // Bước 2: Xác định xem đây là phần tử duy nhất hay một phần của nhóm
                // Thử tìm các phần tử tương tự
                const similarElements = findSimilarElements(bestContainer);
                
                if (similarElements.length >= 3) {
                    console.log(`Tìm thấy ${similarElements.length} phần tử tương tự`);
                    
                    // Tìm selector chung cho các phần tử tương tự
                    const commonSelector = findCommonSelector(similarElements);
                    console.log("Selector chung:", commonSelector);
                    
                    // Áp dụng selector
                    if (commonSelector) {
                        applySelector(commonSelector);
                        return;
                    }
                }
                
                // Nếu không tìm được nhiều phần tử tương tự, thử phương pháp khác
                // Tạo selector cho container đã chọn
                let selector = generateSelector(bestContainer);
                console.log("Selector cho container đã chọn:", selector);
                
                // Kiểm tra xem selector có quá cụ thể không
                const matches = iframeDocument.querySelectorAll(selector);
                
                if (matches.length <= 1) {
                    // Selector quá cụ thể, thử tạo selector đơn giản hơn
                    const simpleSelector = generateSimpleSelector(bestContainer);
                    
                    // Kiểm tra xem selector đơn giản có tìm được nhiều phần tử hơn không
                    const simpleMatches = iframeDocument.querySelectorAll(simpleSelector);
                    
                    if (simpleMatches.length > 1) {
                        console.log("Sử dụng selector đơn giản:", simpleSelector, 
                                    "với", simpleMatches.length, "kết quả");
                        selector = simpleSelector;
                    } else {
                        // Thử tìm selector cha có thể có nhiều phần tử tương tự
                        const parentSelector = findParentWithSimilarChildren(bestContainer);
                        if (parentSelector) {
                            console.log("Sử dụng selector cha:", parentSelector);
                            selector = parentSelector;
                        }
                    }
                }
                
                // Áp dụng selector cuối cùng
                applySelector(selector);
            } else {
                console.log("Không tìm thấy container, sử dụng phần tử được click");
                
                // Sử dụng phần tử được click
                selectedElement = element;
                
                // Tạo selector
                const selector = generateSelector(element);
                applySelector(selector);
            }
        } catch (err) {
            console.error("Lỗi khi xử lý click:", err);
            alert("Không thể tạo selector cho phần tử này. Vui lòng thử chọn phần tử khác.");
        }
    }

    /**
     * Tìm các phần tử tương tự với phần tử đã cho
     */
    function findSimilarElements(element) {
        if (!element) return [];
        
        // Kết quả
        const similarElements = [element];
        
        // Thử các phương pháp khác nhau để tìm phần tử tương tự
        
        // 1. Tìm theo tag name và class name giống nhau
        if (element.className) {
            const classes = element.className.split(' ')
                .filter(cls => cls && !cls.includes('highlight-'));
                
            for (const cls of classes) {
                // Tìm các phần tử có cùng tag và class
                const selector = `${element.tagName.toLowerCase()}.${cls}`;
                const matches = iframeDocument.querySelectorAll(selector);
                
                if (matches.length >= 3 && matches.length <= 100) {
                    console.log(`Tìm thấy ${matches.length} phần tử có cùng tag và class: ${selector}`);
                    
                    // Kiểm tra xem các phần tử có đủ giống nhau không
                    if (areElementsSimilar(matches)) {
                        return Array.from(matches);
                    }
                }
            }
        }
        
        // 2. Tìm các phần tử anh em giống nhau
        const parent = element.parentElement;
        if (parent) {
            const siblings = parent.children;
            
            // Nếu có ít nhất 3 phần tử con và không quá nhiều
            if (siblings.length >= 3 && siblings.length <= 50) {
                // Kiểm tra xem chúng có giống nhau không
                const siblingArray = Array.from(siblings);
                
                // Lọc ra các phần tử con có thẻ giống nhau
                const tagName = element.tagName.toLowerCase();
                const sameTagChildren = siblingArray.filter(child => 
                    child.tagName.toLowerCase() === tagName
                );
                
                // Nếu có ít nhất 3 phần tử con cùng loại
                if (sameTagChildren.length >= 3) {
                    if (areElementsSimilar(sameTagChildren)) {
                        console.log(`Tìm thấy ${siblings.length} phần tử anh em tương tự`);
                        return siblingArray;
                    }
                }
            }
        }
        
        // 3. Tìm phần tử ở cùng level trong DOM có cấu trúc tương tự
        try {
            // Tạo selector đơn giản cho phần tử
            const simpleSelector = generateSimpleSelector(element);
            
            // Tìm tất cả phần tử khớp với selector đơn giản
            const potentialMatches = iframeDocument.querySelectorAll(simpleSelector);
            
            if (potentialMatches.length >= 3 && potentialMatches.length <= 100) {
                console.log(`Tìm thấy ${potentialMatches.length} phần tử tiềm năng với selector: ${simpleSelector}`);
                
                // Lọc ra các phần tử thực sự tương tự
                const matches = Array.from(potentialMatches).filter(el => {
                    return areElementsStructurallySimilar(element, el);
                });
                
                if (matches.length >= 3) {
                    console.log(`Tìm thấy ${matches.length} phần tử tương tự về cấu trúc`);
                    return matches;
                }
            }
        } catch (err) {
            console.warn("Lỗi khi tìm các phần tử cùng cấp:", err);
        }
        
        // Trả về phần tử được chọn nếu không tìm thấy phần tử tương tự
        return similarElements;
    }

    /**
     * Tìm selector cha có thể chứa nhiều phần tử con tương tự
     */
    function findParentWithSimilarChildren(element) {
        let current = element.parentElement;
        let depth = 0;
        const MAX_DEPTH = 4;
        
        while (current && current.tagName !== 'BODY' && current.tagName !== 'HTML' && depth < MAX_DEPTH) {
            const selector = generateSelector(current);
            const parent = iframeDocument.querySelector(selector);
            
            if (parent) {
                // Tìm các phần tử con trực tiếp
                const children = parent.children;
                
                // Kiểm tra xem có nhiều phần tử con giống nhau không
                if (children.length >= 3 && children.length <= 50) {
                    const childrenArray = Array.from(children);
                    
                    // Lọc ra các phần tử con có thẻ giống nhau
                    const tagName = element.tagName.toLowerCase();
                    const sameTagChildren = childrenArray.filter(child => 
                        child.tagName.toLowerCase() === tagName
                    );
                    
                    // Nếu có ít nhất 3 phần tử con cùng loại
                    if (sameTagChildren.length >= 3) {
                        if (areElementsSimilar(sameTagChildren)) {
                            // Tạo selector để chọn tất cả các phần tử con này
                            return `${selector} > ${tagName}`;
                        }
                    }
                }
            }
            
            current = current.parentElement;
            depth++;
        }
        
        return null;
    }

    /**
     * Find the best news/content container parent for a clicked element
     * This helps users select entire news items by clicking anywhere within them
     */
    function findBestNewsContainer(element) {
        if (!element) return null;

        // Nếu phần tử đã là container chính (article, .item, v.v.), sử dụng nó
        if (element.tagName === 'ARTICLE' || 
            (element.className && 
             /\b(item|card|post|article|news-item|entry)\b/i.test(element.className))) {
            return element;
        }
        
        // Tìm container chứa phần tử
        let current = element;
        let bestContainer = null;
        let depth = 0;
        const MAX_DEPTH = 5;
        
        while (current && current.tagName !== 'BODY' && current.tagName !== 'HTML' && depth < MAX_DEPTH) {
            // Kiểm tra các container phổ biến
            if (current.tagName === 'ARTICLE' || 
                current.tagName === 'LI' || 
                (current.className && 
                 /\b(item|card|post|article|news-item|entry)\b/i.test(current.className))) {
                bestContainer = current;
                break;
            }
            
            // Kiểm tra nếu phần tử hiện tại chứa đủ thành phần của một bài báo
            const hasTitle = current.querySelector('h1, h2, h3, h4, h5, h6, .title, .heading');
            const hasLink = current.querySelector('a');
            const hasImage = current.querySelector('img');
            
            if (hasTitle && hasLink && current.offsetHeight > 100) {
                bestContainer = current;
                break;
            }
            
            // Kiểm tra nếu phần tử hiện tại có kích thước phù hợp cho bài báo
            const area = current.offsetWidth * current.offsetHeight;
            if (area > 10000 && area < 300000 && hasLink) {
                // Lưu là container tiềm năng và tiếp tục tìm kiếm
                if (!bestContainer) bestContainer = current;
            }
            
            current = current.parentElement;
            depth++;
        }
        
        // Nếu không tìm thấy container phù hợp, sử dụng phần tử được click
        return bestContainer || element;
    }

    /**
     * Generate a simple selector for an element (fallback for complex cases)
     */
    function generateSimpleSelector(element) {
        if (element.id) {
            return `#${element.id}`;
        }

        // Try with tag and class
        if (element.className && typeof element.className === 'string') {
            const classes = element.className.split(' ')
                .filter(cls => cls && !cls.includes('highlight-'));

            if (classes.length > 0) {
                return `${element.tagName.toLowerCase()}.${classes[0]}`;
            }
        }

        // Just use tag name
        return element.tagName.toLowerCase();
    }

    /**
     * Generate a CSS selector for an element that includes the full path
     * This creates selectors like div > div > div > h3 > a
     */
    function generateSelector(element) {
        try {
            // First check if element has an ID
            if (element.id && element.id.length > 0 && !element.id.includes(' ')) {
                return `#${element.id}`;
            }
            
            // Check if element has useful classes
            if (element.className && typeof element.className === 'string') {
                const classes = element.className.split(' ')
                    .filter(cls => cls && !cls.includes('highlight-'));
                    
                // Nếu phần tử có class rõ ràng, dùng class đầu tiên
                if (classes.length > 0) {
                    // Tạo selector với một class đặc trưng
                    const cls = classes[0];
                    
                    // Kiểm tra xem class này có phổ biến trên trang không
                    const sameClassCount = iframeDocument.querySelectorAll(`.${cls}`).length;
                    
                    // Nếu class này là duy nhất hoặc ít phổ biến, sử dụng nó
                    if (sameClassCount < 10) {
                        return `.${cls}`;
                    }
                    
                    // Nếu class phổ biến, thêm thẻ vào trước
                    const tagWithClass = `${element.tagName.toLowerCase()}.${cls}`;
                    if (iframeDocument.querySelectorAll(tagWithClass).length < 10) {
                        return tagWithClass;
                    }
                }
            }
            
            // Xây dựng selector dựa trên parent > child relationship
            let path = [];
            let current = element;
            let maxLength = 4; // Giới hạn độ dài đường dẫn để tránh quá cụ thể
            
            while (current && current.tagName !== 'BODY' && current.tagName !== 'HTML' && path.length < maxLength) {
                let selector = current.tagName.toLowerCase();
                
                // Thêm vào đường dẫn
                path.unshift(selector);
                
                // Kiểm tra xem selector hiện tại đã đủ cụ thể chưa
                const currentPath = path.join(' > ');
                const matchCount = iframeDocument.querySelectorAll(currentPath).length;
                
                // Nếu đã đủ cụ thể (ít hơn 10 kết quả), dừng lại
                if (matchCount < 10) {
                    break;
                }
                
                // Di chuyển lên parent
                current = current.parentElement;
            }
            
            // Kết hợp đường dẫn với child combinator
            return path.join(' > ');
        } catch (err) {
            console.error("Lỗi khi tạo selector:", err);
            return generateSimpleSelector(element);
        }
    }

    /**
     * Build content mapping based on the selected element
     * This tries to identify title, link, description, image, and date elements
     */
    function buildContentMapping(element) {
        console.log("Building content mapping from element:", element.tagName);
        
        // Create base mapping with container selector
        const mapping = {
            container: generateSelector(element),
            title: '',
            link: '',
            description: '',
            image: '',
            date: ''
        };
        
        try {
            // Phân tích cấu trúc trang - đặc biệt hỗ trợ các nền tảng phổ biến
            const domainName = window.location.hostname || 
                              (document.querySelector('meta[property="og:site_name"]')?.content) || '';
            
            console.log("Analyzing domain:", domainName);
            
            // Kiểm tra nếu là trang beehiiv
            const isBeehiiv = domainName.includes('beehiiv') || 
                              element.innerHTML.includes('beehiiv') ||
                              iframeDocument.querySelector('a[href*="beehiiv"]');
            
            if (isBeehiiv) {
                console.log("Detected beehiiv platform, using specialized mapping");
                return buildBeehiivMapping(element, mapping);
            }
            
            // Thử phân tích theo cấu trúc chung
            // 1. Tìm tiêu đề - ưu tiên trong heading tags và link chính
            const headings = element.querySelectorAll('h1, h2, h3, h4, h5, h6, .title, .heading, [class*="title"], [class*="heading"]');
            let titleElement = null;
            
            if (headings.length > 0) {
                // Lấy heading phù hợp nhất làm tiêu đề
                titleElement = headings[0];
                // Có thể là link trong heading
                const linkInHeading = titleElement.querySelector('a');
                if (linkInHeading) {
                    titleElement = linkInHeading;
                    mapping.link = generateSelector(linkInHeading);
                }
                mapping.title = generateSelector(titleElement);
            }
            
            // 2. Tìm link chính nếu chưa tìm thấy
            if (!mapping.link) {
                // Tìm tất cả link và text có độ dài phù hợp
                const allLinks = Array.from(element.querySelectorAll('a')).filter(a => 
                    a.textContent.trim().length > 10 && a.textContent.trim().length < 200
                );
                
                if (allLinks.length > 0) {
                    let bestLink = null;
                    let bestLength = 0;
                    
                    // Ưu tiên link dài hơn và chứa text
                    for (const link of allLinks) {
                        const text = link.textContent.trim();
                        if (text.length > bestLength) {
                            bestLength = text.length;
                            bestLink = link;
                        }
                    }
                    
                    if (bestLink) {
                        mapping.link = generateSelector(bestLink);
                        
                        // Nếu chưa có tiêu đề, sử dụng link làm tiêu đề
                        if (!mapping.title && bestLink.textContent.trim()) {
                            mapping.title = mapping.link;
                        }
                    }
                }
            }
            
            // 3. Tìm hình ảnh
            const images = element.querySelectorAll('img');
            if (images.length > 0) {
                // Chọn ảnh lớn nhất
                let bestImage = images[0];
                let largestArea = 0;
                
                for (const img of images) {
                    const width = img.naturalWidth || img.width || 0;
                    const height = img.naturalHeight || img.height || 0;
                    const area = width * height;
                    
                    // Bỏ qua các ảnh nhỏ hoặc icon
                    if (area > largestArea && img.src && !img.src.match(/icon|logo|avatar|placeholder|blank\.(gif|png|jpg)/i)) {
                        largestArea = area;
                        bestImage = img;
                    }
                }
                
                mapping.image = generateSelector(bestImage);
            }
            
            // 4. Tìm mô tả
            let descElement = null;
            
            // Tìm trong các phần tử có class hoặc id chứa từ khóa liên quan
            const descSelectors = [
                '.summary', '.excerpt', '.description', '.desc', '.content', '.text',
                '[class*="summary"]', '[class*="excerpt"]', '[class*="description"]', 
                '[class*="content"]', '[class*="text"]',
                'p.lead', '.lead', '.subtitle', '.preview'
            ];
            
            for (const selector of descSelectors) {
                const elements = element.querySelectorAll(selector);
                if (elements.length > 0) {
                    for (const el of elements) {
                        // Bỏ qua phần tử quá ngắn hoặc quá dài
                        const text = el.textContent.trim();
                        if (text.length >= 20 && text.length <= 500) {
                            descElement = el;
                            break;
                        }
                    }
                    if (descElement) break;
                }
            }
            
            // Nếu không tìm thấy bằng class, tìm trong p tags
            if (!descElement) {
                const paragraphs = element.querySelectorAll('p');
                if (paragraphs.length > 0) {
                    // Lấy đoạn văn có độ dài hợp lý, không phải title hoặc date
                    for (const p of paragraphs) {
                        const text = p.textContent.trim();
                        // Bỏ qua đoạn văn quá ngắn
                        if (text.length < 20) continue;
                        
                        // Bỏ qua nếu đoạn văn là phần tử tiêu đề hoặc ngày tháng
                        if (p === titleElement) continue;
                        if (/^\d{1,2}[\/\.-]\d{1,2}[\/\.-]\d{2,4}$/.test(text)) continue;
                        
                        descElement = p;
                        break;
                    }
                }
            }
            
            // Tìm trong div, span nếu không tìm thấy trong p
            if (!descElement) {
                const textContainers = element.querySelectorAll('div, span');
                for (const container of textContainers) {
                    // Bỏ qua container quá phức tạp
                    if (container.children.length > 3) continue;
                    
                    const text = container.textContent.trim();
                    // Cần length dài hợp lý cho mô tả
                    if (text.length > 30 && text.length < 300) {
                        // Bỏ qua nếu là tiêu đề hoặc ngày tháng
                        if (container === titleElement) continue;
                        if (/^\d{1,2}[\/\.-]\d{1,2}[\/\.-]\d{2,4}$/.test(text)) continue;
                        
                        descElement = container;
                        break;
                    }
                }
            }
            
            if (descElement) {
                mapping.description = generateSelector(descElement);
            }
            
            // 5. Tìm ngày tháng - phương pháp cải tiến
            // Ưu tiên tìm trong thẻ time và các phần tử có attribute datetime
            const timeElements = element.querySelectorAll('time, [datetime]');
            if (timeElements.length > 0) {
                mapping.date = generateSelector(timeElements[0]);
            } else {
                // Tìm theo text có format như ngày tháng
                const dateRegex = [
                    /\b\d{1,2}[\/\.-]\d{1,2}[\/\.-]\d{2,4}\b/, // DD/MM/YYYY
                    /\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]* \d{1,2},? \d{2,4}\b/i, // Month DD, YYYY
                    /\b\d{1,2} (hours|days|weeks|months) ago\b/i, // X time ago
                    /\b\d{1,2} (giờ|ngày|tuần|tháng) trước\b/i, // X time ago (Vietnamese)
                    /\b(giờ|phút|ngày|tuần) trước\b/i, // time ago (Vietnamese)
                    /\bposted:? \d/i, // Posted: date
                    /\bpublished:? \d/i // Published: date
                ];
                
                // Tìm tất cả phần tử text có thể chứa ngày tháng
                const allTextElements = element.querySelectorAll('*');
                let dateElement = null;
                
                for (const el of allTextElements) {
                    // Bỏ qua phần tử có nhiều con
                    if (el.children.length > 2) continue;
                    
                    const text = el.textContent.trim();
                    if (text.length < 50) { // Giới hạn độ dài để tránh text dài
                        for (const regex of dateRegex) {
                            if (regex.test(text)) {
                                dateElement = el;
                                break;
                            }
                        }
                        
                        // Kiểm tra dựa trên từ khóa thời gian
                        if (!dateElement && 
                            (text.includes('ago') || 
                             text.includes('hours') || 
                             text.includes('days') || 
                             text.includes('trước') || 
                             /^\d+ (min|hour|day|week|month|year)s?$/.test(text))) {
                            dateElement = el;
                        }
                    }
                    
                    if (dateElement) break;
                }
            }
            
            // Tìm theo class/id chứa từ khóa date
            if (!dateElement) {
                const dateSelectors = [
                    '.date', '.time', '.meta-date', '.timestamp', '.published',
                    '[class*="date"]', '[class*="time"]', '[class*="meta"]',
                    '.post-date', '.entry-date', '.publish-date'
                ];
                
                for (const selector of dateSelectors) {
                    const elements = element.querySelectorAll(selector);
                    if (elements.length > 0) {
                        dateElement = elements[0];
                        break;
                    }
                }
            }
            
            if (dateElement) {
                mapping.date = generateSelector(dateElement);
            }
        } catch (err) {
            console.error("Lỗi khi phát hiện cấu trúc nội dung:", err);
        }
        
        console.log("Kết quả mapping:", mapping);
        return mapping;
    }

    /**
     * Xây dựng mapping cho nền tảng beehiiv
     */
    function buildBeehiivMapping(element, baseMapping) {
        const mapping = {...baseMapping};
        
        try {
            // Phân tích cấu trúc beehiiv
            // 1. Xác định article container nếu chưa phải
            let articleContainer = element;
            
            // Nếu phần tử không phải là article container, tìm nó
            if (!element.querySelector('a[href*="/p/"]') && !element.matches('article')) {
                // Tìm article container gần nhất
                let parent = element.parentElement;
                let depth = 0;
                while (parent && depth < 5) {
                    if (parent.querySelector('a[href*="/p/"]') || 
                        parent.matches('article') ||
                        parent.className.includes('post') ||
                        parent.className.includes('article')) {
                        articleContainer = parent;
                        break;
                    }
                    parent = parent.parentElement;
                    depth++;
                }
            }
            
            // 2. Tìm link của bài viết (đặc trưng của beehiiv là links dạng /p/...)
            const links = articleContainer.querySelectorAll('a[href*="/p/"]');
            if (links.length > 0) {
                const mainLink = links[0];
                mapping.link = generateSelector(mainLink);
                
                // Tiêu đề thường là nội dung của link hoặc heading gần nhất
                mapping.title = mapping.link; // Dùng link làm tiêu đề
            } else {
                // Tìm link thông thường nếu không có link dạng /p/
                const allLinks = articleContainer.querySelectorAll('a');
                
                if (allLinks.length > 0) {
                    // Tìm link có text dài nhất (có thể là tiêu đề)
                    let bestLink = null;
                    let maxLength = 0;
                    
                    for (const link of allLinks) {
                        const text = link.textContent.trim();
                        if (text.length > maxLength && text.length > 10) {
                            maxLength = text.length;
                            bestLink = link;
                        }
                    }
                    
                    if (bestLink) {
                        mapping.link = generateSelector(bestLink);
                        mapping.title = mapping.link;
                    }
                }
            }
            
            // 3. Tìm ngày tháng - beehiiv thường đặt ngày ở đầu tiêu đề hoặc trong một phần tử riêng
            const dateTexts = articleContainer.querySelectorAll('span, small, div');
            let dateElement = null;
            
            // Định dạng phổ biến: "X hours ago", "May 12, 2025", etc.
            const datePatterns = [
                /\b\d+ (hour|day|week|month)s? ago\b/i,
                /\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s+\d{1,2},?\s+\d{4}\b/i
            ];
            
            for (const el of dateTexts) {
                const text = el.textContent.trim();
                
                // Nếu text ngắn và khớp với pattern ngày tháng
                if (text.length < 50) {
                    if (datePatterns.some(pattern => pattern.test(text))) {
                        dateElement = el;
                        break;
                    }
                }
            }
            
            // Nếu không tìm thấy, thử với text element đầu tiên khớp với pattern
            if (!dateElement) {
                const walker = document.createTreeWalker(
                    articleContainer,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );
                
                let node;
                while (node = walker.nextNode()) {
                    const text = node.textContent.trim();
                    if (text && text.length < 50) {
                        if (datePatterns.some(pattern => pattern.test(text))) {
                            dateElement = node.parentElement;
                            break;
                        }
                    }
                }
            }
            
            if (dateElement) {
                mapping.date = generateSelector(dateElement);
            }
            
            // 4. Tìm hình ảnh
            const images = articleContainer.querySelectorAll('img');
            if (images.length > 0) {
                // Lấy ảnh lớn nhất không phải avatar
                let bestImage = null;
                let maxArea = 0;
                
                for (const img of images) {
                    // Bỏ qua avatar và icon
                    if (img.src.includes('avatar') || 
                        img.width < 50 || 
                        img.height < 50 ||
                        img.className.includes('avatar') ||
                        img.className.includes('icon')) {
                        continue;
                    }
                    
                    const area = (img.naturalWidth || img.width) * (img.naturalHeight || img.height);
                    if (area > maxArea) {
                        maxArea = area;
                        bestImage = img;
                    }
                }
                
                if (bestImage) {
                    mapping.image = generateSelector(bestImage);
                }
            }
            
            // 5. Mô tả - beehiiv thường không có mô tả riêng, có thể dùng đoạn đầu tiêu đề
            // hoặc đoạn văn đầu tiên
            const paragraphs = articleContainer.querySelectorAll('p');
            if (paragraphs.length > 0) {
                for (const p of paragraphs) {
                    const text = p.textContent.trim();
                    if (text.length > 20 && text.length < 300) {
                        mapping.description = generateSelector(p);
                        break;
                    }
                }
            }
            
        } catch (err) {
            console.error("Lỗi khi xây dựng beehiiv mapping:", err);
        }
        
        console.log("Beehiiv mapping result:", mapping);
        return mapping;
    }

    /**
     * Show a preview of the selector
     */
    function showSelectorPreview(selector) {
        // Update the selector text in the status area
        if (selectorText) {
            selectorText.textContent = selector;
        }

        // Update the input field
        if (cssSelector) {
            cssSelector.value = selector;
        }
    }

    /**
     * Apply a selector and update the UI
     */
    function applySelector(selector) {
        if (!selector) return;

        console.log("Áp dụng selector:", selector);

        // Store the current selector
        currentSelector = selector;

        // Update the selector input and display
        if (cssSelector) cssSelector.value = selector;
        if (selectorText) selectorText.textContent = selector;

        // Remove all highlights
        removeAllHighlights();

        // Find elements matching the selector
        try {
            const matchingElements = iframeDocument.querySelectorAll(selector);
            console.log(`Tìm thấy ${matchingElements.length} phần tử phù hợp với selector: ${selector}`);

            // Mark selected element
            if (selectedElement) {
                selectedElement.classList.add('highlight-selected');
            }

            // Highlight similar elements and add to selected elements
            selectedElements = [];
            matchingElements.forEach(el => {
                // Add to selectedElements array
                selectedElements.push(el);

                // Add highlight class if not the originally selected element
                if (el !== selectedElement) {
                    el.classList.add('highlight-similar');
                }
            });

            console.log(`Đã thêm ${selectedElements.length} phần tử vào mảng selectedElements`);

            // Update the count displays
            if (selectedCount) selectedCount.textContent = selectedElements.length;
            if (itemsCount) itemsCount.textContent = `(${selectedElements.length})`;

            // Xây dựng mapping data từ phần tử đầu tiên
            if (selectedElements.length > 0) {
                try {
                    // Nếu đã chọn một phần tử cụ thể, sử dụng phần tử đó để làm mapping
                    // Nếu không, sử dụng phần tử đầu tiên trong danh sách
                    const mappingElement = selectedElement || selectedElements[0];
                    
                    mapData = buildContentMapping(mappingElement);
                    console.log("Đã xây dựng mapping data:", mapData);
                    
                    // Nếu mapping không thành công, thử với phần tử đầu tiên trong danh sách
                    if (!mapData.title && !mapData.link && mappingElement !== selectedElements[0]) {
                        console.log("Thử xây dựng mapping với phần tử đầu tiên...");
                        mapData = buildContentMapping(selectedElements[0]);
                    }
                    
                    // Hiển thị thông tin mapping nếu có
                    displayMappingInfo();
                } catch (err) {
                    console.error("Lỗi khi xây dựng mapping:", err);
                    // Tạo mapping cơ bản với container
                    mapData = {
                        container: selector,
                        title: '',
                        link: '',
                        description: '',
                        image: '',
                        date: ''
                    };
                }
            }

            // Display matching entries
            displayMatchingEntries(selectedElements);

        } catch (e) {
            console.error('Lỗi khi áp dụng selector:', e);

            // Show detailed error message
            const errorMsg = `Lỗi selector: "${selector}" không hợp lệ. ${e.message}`;
            console.error(errorMsg);

            if (matchingEntries) {
                matchingEntries.innerHTML = `<div class="entry-item">
                    <div class="entry-title">Selector không hợp lệ. Vui lòng chọn phần tử khác.</div>
                    <div class="entry-description">${errorMsg}</div>
                </div>`;
            }

            // Try to generate a simpler selector
            if (selectedElement) {
                const simpleSelector = generateSimpleSelector(selectedElement);
                console.log("Thử với selector đơn giản hơn:", simpleSelector);

                if (simpleSelector !== selector) {
                    setTimeout(() => {
                        applySelector(simpleSelector);
                    }, 100);
                }
            }
        }
    }

    /**
     * Display selector mapping information in the UI
     */
    function displayMappingInfo() {
        const mappingContainer = document.getElementById('selector-mapping');
        if (!mappingContainer) return;

        // Create mapping display HTML
        let html = '';

        if (mapData) {
            // Main container selector
            html += createMappingItem('Container', mapData.container);

            // Title selector
            if (mapData.title) {
                html += createMappingItem('Title', mapData.title);
            }

            // Link selector
            if (mapData.link) {
                html += createMappingItem('Link', mapData.link);
            }

            // Description selector
            if (mapData.description) {
                html += createMappingItem('Description', mapData.description);
            }

            // Image selector
            if (mapData.image) {
                html += createMappingItem('Image', mapData.image);
            }

            // Date selector
            if (mapData.date) {
                html += createMappingItem('Date', mapData.date);
            }
        } else {
            html = '<div class="mapping-item">Chọn một phần tử để xem mapping data</div>';
        }

        // Update the container
        mappingContainer.innerHTML = html;
    }

    /**
     * Create a mapping item HTML
     */
    function createMappingItem(label, value) {
        return `
            <div class="mapping-item">
                <div class="mapping-label">${label}:</div>
                <div class="mapping-value">${value}</div>
            </div>
        `;
    }

    /**
     * Remove all highlight classes from elements
     */
    function removeAllHighlights() {
        if (!iframeDocument) return;

        const highlighted = iframeDocument.querySelectorAll('.highlight-hover, .highlight-selected, .highlight-similar');
        highlighted.forEach(el => {
            el.classList.remove('highlight-hover', 'highlight-selected', 'highlight-similar');
        });
    }

    /**
     * Extract content from an element
     */
    function extractElementContent(element) {
        // Initialize content object
        const content = {
            title: '',
            link: '',
            description: '',
            imageUrl: '',
            date: ''
        };

        // If we have mapping data, use it to extract content more precisely
        if (mapData) {
            try {
                // Try to extract title using the mapping
                if (mapData.title) {
                    const titleSelector = mapData.title;
                    let titleEl;
                    
                    // Case 1: Element itself matches the title selector
                    if (element.matches(titleSelector)) {
                        titleEl = element;
                    } else {
                        // Case 2: Find title element within this element
                        titleEl = element.querySelector(titleSelector);
                    }
                    
                    if (titleEl) {
                        content.title = titleEl.textContent.trim();
                        
                        // Nếu tiêu đề có chứa thời gian ở đầu (như trong beehiiv)
                        // Format: "19 hours ago Google I/O Teases "Gemini Everywhere""
                        const titleMatch = content.title.match(/^(\d+ \w+ ago|\w+ \d{1,2},? \d{4})(.*)/i);
                        if (titleMatch) {
                            content.date = titleMatch[1].trim();
                            content.title = titleMatch[2].trim();
                        }
                    }
                }

                // Try to extract link
                if (mapData.link) {
                    const linkSelector = mapData.link;
                    let linkEl;
                    
                    if (element.matches(linkSelector)) {
                        linkEl = element;
                    } else {
                        linkEl = element.querySelector(linkSelector);
                    }
                    
                    if (linkEl && linkEl.tagName === 'A') {
                        content.link = linkEl.href;
                        
                        // Nếu không có tiêu đề, dùng text của link
                        if (!content.title && linkEl.textContent.trim()) {
                            content.title = linkEl.textContent.trim();
                            
                            // Kiểm tra lại format "date title"
                            const titleMatch = content.title.match(/^(\d+ \w+ ago|\w+ \d{1,2},? \d{4})(.*)/i);
                            if (titleMatch) {
                                content.date = titleMatch[1].trim();
                                content.title = titleMatch[2].trim();
                            }
                        }
                    }
                }

                // Try to extract description
                if (mapData.description) {
                    const descSelector = mapData.description;
                    let descEl;
                    
                    if (element.matches(descSelector)) {
                        descEl = element;
                    } else {
                        descEl = element.querySelector(descSelector);
                    }
                    
                    if (descEl) {
                        content.description = descEl.textContent.trim();
                    }
                }

                // Try to extract image
                if (mapData.image) {
                    const imgSelector = mapData.image;
                    let imgEl;
                    
                    if (element.matches(imgSelector)) {
                        imgEl = element;
                    } else {
                        imgEl = element.querySelector(imgSelector);
                    }
                    
                    if (imgEl && imgEl.tagName === 'IMG') {
                        content.imageUrl = imgEl.src;
                    }
                }

                // Try to extract date
                if (mapData.date) {
                    const dateSelector = mapData.date;
                    let dateEl;
                    
                    if (element.matches(dateSelector)) {
                        dateEl = element;
                    } else {
                        dateEl = element.querySelector(dateSelector);
                    }
                    
                    if (dateEl) {
                        content.date = dateEl.textContent.trim();
                        // Check for datetime attribute
                        if (dateEl.getAttribute('datetime')) {
                            content.date = dateEl.getAttribute('datetime');
                        }
                    }
                }
            } catch (e) {
                console.warn('Error extracting content with mapping', e);
            }
        }

        // Fallback extraction methods if mapping didn't work
        if (!content.title) {
            // Extract title through common patterns
            // 1. Look for headings
            const headings = element.querySelectorAll('h1, h2, h3, h4, h5, h6');
            if (headings.length > 0) {
                content.title = headings[0].textContent.trim();
            } else {
                // 2. Try with prominent links
                const links = Array.from(element.querySelectorAll('a')).filter(a => 
                    a.textContent.trim().length > 10
                );
                
                if (links.length > 0) {
                    // Sort by text length to find most substantial link
                    links.sort((a, b) => 
                        b.textContent.trim().length - a.textContent.trim().length
                    );
                    content.title = links[0].textContent.trim();
                    
                    // Also extract link if we haven't already
                    if (!content.link) {
                        content.link = links[0].href;
                    }
                } else {
                    // 3. Fallback: use first substantial text content
                    content.title = element.textContent.trim().substring(0, 100);
                    if (content.title.length === 100) content.title += '...';
                }
            }
            
            // Kiểm tra nếu tiêu đề có định dạng "date title"
            const titleMatch = content.title.match(/^(\d+ \w+ ago|\w+ \d{1,2},? \d{4})(.*)/i);
            if (titleMatch) {
                // Extract date and clean title
                if (!content.date) {
                    content.date = titleMatch[1].trim();
                }
                content.title = titleMatch[2].trim();
            }
        }

        // Extract link if not already found
        if (!content.link) {
            // Look for most prominent links
            const allLinks = element.querySelectorAll('a[href]:not([href="#"]):not([href=""])');
            if (allLinks.length > 0) {
                // Prefer links with URLs containing /p/ or /post/ pattern (common in blogs)
                const postLinks = Array.from(allLinks).filter(link => 
                    link.href.includes('/p/') || 
                    link.href.includes('/post/') ||
                    link.href.includes('/article/')
                );
                
                if (postLinks.length > 0) {
                    content.link = postLinks[0].href;
                } else {
                    // Fallback to first substantial link
                    content.link = allLinks[0].href;
                }
            }
        }

        // Extract description if not already found
        if (!content.description) {
            // Try with paragraphs first
            const paragraphs = element.querySelectorAll('p');
            if (paragraphs.length > 0) {
                // Filter out very short paragraphs and those that look like dates
                const substantialParagraphs = Array.from(paragraphs).filter(p => {
                    const text = p.textContent.trim();
                    return text.length > 20 && !/^\d+ (hours|days) ago$/.test(text);
                });
                
                if (substantialParagraphs.length > 0) {
                    content.description = substantialParagraphs[0].textContent.trim();
                } else {
                    content.description = paragraphs[0].textContent.trim();
                }
            } else {
                // Fallback: extract text that's not in headings or links
                const clone = element.cloneNode(true);
                // Remove headings and links from clone to extract remaining text
                clone.querySelectorAll('h1, h2, h3, h4, h5, h6, a').forEach(el => el.remove());
                
                const text = clone.textContent.trim();
                if (text.length > 0) {
                    content.description = text.substring(0, 200);
                    if (content.description.length === 200) content.description += '...';
                }
            }
        }

        // Extract image if not already found
        if (!content.imageUrl) {
            // Look for substantive images (not tiny icons)
            const images = Array.from(element.querySelectorAll('img')).filter(img => {
                const width = img.naturalWidth || img.width || 0;
                const height = img.naturalHeight || img.height || 0;
                
                // Skip tiny images or icons
                return width >= 60 && height >= 60 && 
                       !img.src.match(/icon|logo|avatar|blank\.(gif|png|jpg)/i);
            });
            
            if (images.length > 0) {
                content.imageUrl = images[0].src;
            }
        }

        // Extract date if not already found
        if (!content.date) {
            // Check for time elements, elements with datetime attributes, or common date classes
            const dateElements = element.querySelectorAll('time, [datetime], .date, .time, .meta, [class*="date"], [class*="time"]');
            
            if (dateElements.length > 0) {
                const dateEl = dateElements[0];
                
                // Prefer datetime attribute if available
                if (dateEl.hasAttribute('datetime')) {
                    content.date = dateEl.getAttribute('datetime');
                } else {
                    content.date = dateEl.textContent.trim();
                }
            } else {
                // Search for text patterns that look like dates
                const allText = element.innerText;
                
                // Common date patterns
                const patterns = [
                    /\b\d+ (hour|day|week|month)s? ago\b/i,
                    /\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s+\d{1,2},?\s+\d{4}\b/i,
                    /\b\d{1,2}[\/\.-]\d{1,2}[\/\.-]\d{2,4}\b/
                ];
                
                for (const pattern of patterns) {
                    const match = allText.match(pattern);
                    if (match) {
                        content.date = match[0];
                        break;
                    }
                }
            }
        }

        // Clean up any entities in the content
        if (content.title) content.title = content.title.replace(/&nbsp;/g, ' ').trim();
        if (content.description) content.description = content.description.replace(/&nbsp;/g, ' ').trim();
        
        return content;
    }

    /**
     * Display matching entries in the sidebar
     */
    function displayMatchingEntries(elements) {
        if (!matchingEntries) return;

        // Clear existing entries
        matchingEntries.innerHTML = '';

        if (elements.length === 0) {
            matchingEntries.innerHTML = '<div class="entry-item"><div class="entry-title">Không tìm thấy phần tử nào phù hợp</div></div>';
            return;
        }

        // Lọc các phần tử để loại bỏ dữ liệu rác
        const filteredElements = filterJunkElements(elements);
        
        console.log(`Đã lọc từ ${elements.length} xuống ${filteredElements.length} phần tử sau khi loại bỏ dữ liệu rác`);
        
        if (filteredElements.length === 0) {
            matchingEntries.innerHTML = '<div class="entry-item"><div class="entry-title">Không tìm thấy phần tử chất lượng sau khi lọc</div></div>';
            return;
        }

        // Xử lý các phần tử đã lọc
        const processedEntries = [];
        const usedUrls = new Set(); // Theo dõi URL đã sử dụng để tránh trùng lặp
        
        // Process each element
        filteredElements.forEach((element, index) => {
            const content = extractElementContent(element);
            
            // Bỏ qua phần tử không có đủ thông tin cần thiết
            if (!content.title || content.title.length < 5) {
                return;
            }
            
            // Bỏ qua mục trùng lặp URL
            if (content.link && usedUrls.has(content.link)) {
                return;
            }
            
            // Thêm URL vào danh sách đã sử dụng
            if (content.link) {
                usedUrls.add(content.link);
            }
            
            // Get the full selector path for this element
            const fullSelector = generateSelector(element);

            // Lưu nội dung đã xử lý
            processedEntries.push({
                element: element,
                content: content,
                selector: fullSelector,
                index: index
            });
        });
        
        console.log(`Hiển thị ${processedEntries.length} mục sau khi lọc trùng lặp`);
        
        // Sắp xếp kết quả để hiển thị các mục có thông tin đầy đủ hơn trước
        processedEntries.sort((a, b) => {
            // Ưu tiên mục có đầy đủ thông tin nhất
            const scoreA = getEntryCompletionScore(a.content);
            const scoreB = getEntryCompletionScore(b.content);
            return scoreB - scoreA;
        });

        // Tạo và hiển thị các mục đã lọc
        processedEntries.forEach(entry => {
            // Create entry HTML
            const entryDiv = document.createElement('div');
            entryDiv.className = 'entry-item';
            entryDiv.dataset.index = entry.index;

            // Add entry title with link
            const titleDiv = document.createElement('div');
            titleDiv.className = 'entry-title';
            
            // If we have a link, make the title clickable
            if (entry.content.link) {
                const titleLink = document.createElement('a');
                titleLink.href = entry.content.link;
                titleLink.target = '_blank'; // Open in new tab
                titleLink.textContent = entry.content.title || 'Không có tiêu đề';
                titleLink.title = 'Mở liên kết trong tab mới';
                titleDiv.appendChild(titleLink);
            } else {
                titleDiv.textContent = entry.content.title || 'Không có tiêu đề';
            }
            
            entryDiv.appendChild(titleDiv);
            
            // Add selector info
            const selectorDiv = document.createElement('div');
            selectorDiv.className = 'entry-selector';
            
            // Add icon
            const icon = document.createElement('i');
            icon.className = 'fas fa-code';
            selectorDiv.appendChild(icon);
            
            // Add space after icon
            selectorDiv.appendChild(document.createTextNode(' '));
            
            // Add selector text
            const selectorText = document.createElement('span');
            selectorText.className = 'selector-text';
            selectorText.textContent = entry.selector;
            selectorText.title = entry.selector;
            
            selectorDiv.appendChild(selectorText);
            entryDiv.appendChild(selectorDiv);

            // Add link if available in clickable format
            if (entry.content.link) {
                const linkWrapper = document.createElement('div');
                linkWrapper.className = 'entry-url-wrapper';
                
                // Add icon
                const icon = document.createElement('i');
                icon.className = 'fas fa-link';
                linkWrapper.appendChild(icon);
                
                // Add space after icon
                linkWrapper.appendChild(document.createTextNode(' '));
                
                // Add clickable link
                const linkElement = document.createElement('a');
                linkElement.href = entry.content.link;
                linkElement.className = 'entry-url';
                linkElement.target = '_blank';
                
                // Shorten link for display if too long
                const displayUrl = entry.content.link.length > 40 ? 
                    entry.content.link.substring(0, 37) + '...' : 
                    entry.content.link;
                linkElement.textContent = displayUrl;
                linkElement.title = entry.content.link;
                
                linkWrapper.appendChild(linkElement);
                entryDiv.appendChild(linkWrapper);
            }

            // Add description if available with icon
            if (entry.content.description) {
                const descWrapper = document.createElement('div');
                descWrapper.className = 'entry-description-wrapper';
                
                // Add icon
                const icon = document.createElement('i');
                icon.className = 'fas fa-align-left';
                descWrapper.appendChild(icon);
                
                // Add space after icon
                descWrapper.appendChild(document.createTextNode(' '));
                
                // Add description text
                const descText = document.createElement('span');
                descText.className = 'entry-description';
                
                // Truncate description if too long
                const maxLength = 120;
                descText.textContent = entry.content.description.length > maxLength ? 
                    entry.content.description.substring(0, maxLength) + '...' : 
                    entry.content.description;
                descText.title = entry.content.description;
                
                descWrapper.appendChild(descText);
                entryDiv.appendChild(descWrapper);
            }

            // Add image if available
            if (entry.content.imageUrl) {
                const imgWrapper = document.createElement('div');
                imgWrapper.className = 'entry-image-wrapper';
                
                const img = document.createElement('img');
                img.className = 'entry-image';
                img.src = entry.content.imageUrl;
                img.alt = entry.content.title;
                img.title = 'Ảnh đại diện tin';
                img.loading = 'lazy'; // Lazy load images
                img.onerror = function() { this.style.display = 'none'; };
                
                imgWrapper.appendChild(img);
                entryDiv.appendChild(imgWrapper);
            }

            // Add date if available with icon
            if (entry.content.date) {
                const dateWrapper = document.createElement('div');
                dateWrapper.className = 'entry-date-wrapper';
                
                // Add icon
                const icon = document.createElement('i');
                icon.className = 'fas fa-calendar-alt';
                dateWrapper.appendChild(icon);
                
                // Add space after icon
                dateWrapper.appendChild(document.createTextNode(' '));
                
                // Add date text
                const dateText = document.createElement('span');
                dateText.className = 'entry-date';
                dateText.textContent = entry.content.date;
                
                dateWrapper.appendChild(dateText);
                entryDiv.appendChild(dateWrapper);
            }

            // Add click handler to highlight the corresponding element
            entryDiv.addEventListener('click', (e) => {
                // If clicked on a link, don't handle the event here
                if (e.target.tagName === 'A') {
                    return;
                }
                
                // Remove existing highlights
                removeAllHighlights();

                // Highlight the clicked element
                entry.element.classList.add('highlight-selected');

                // Scroll to the element in the iframe
                entry.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });

            // Add to the matching entries container
            matchingEntries.appendChild(entryDiv);
        });

        // Update the title based on the first item's title
        if (processedEntries.length > 0) {
            const firstItemContent = processedEntries[0].content;
            const feedTitle = document.getElementById('feed-title');
            if (feedTitle && firstItemContent.title) {
                const urlInput = document.getElementById('url-input');
                const domain = urlInput ? new URL(urlInput.value).hostname : 'Website';
                feedTitle.value = `${domain} - ${firstItemContent.title.substring(0, 30)}`;
                if (firstItemContent.title.length > 30) {
                    feedTitle.value += '...';
                }
            }
        }
    }

    /**
     * Lọc các phần tử để loại bỏ dữ liệu rác
     */
    function filterJunkElements(elements) {
        if (!elements || elements.length === 0) return [];
        
        return elements.filter(element => {
            // Bỏ qua phần tử không khả kiến
            if (element.offsetParent === null) return false;
            
            // Bỏ qua phần tử quá nhỏ
            if (element.offsetWidth < 50 || element.offsetHeight < 30) return false;
            
            // Bỏ qua phần tử không chứa nội dung hữu ích
            if (!element.textContent || element.textContent.trim().length < 10) return false;
            
            // Bỏ qua phần tử không có link
            const hasLink = element.querySelector('a[href]:not([href="#"])');
            if (!hasLink) return false;
            
            // Bỏ qua phần tử có text quá giống nhau (có thể là menu/navigation)
            if (isNavigationMenu(element)) return false;
            
            // Bỏ qua các phần tử thuộc về navigation, footer, header
            if (element.closest('nav, footer, .footer, .navbar, .navigation, .menu, .header-nav, .nav-menu')) return false;
            
            // Bỏ qua các phần tử có nhiều link cùng style (menu)
            const links = element.querySelectorAll('a');
            if (links.length > 5 && areAllLinksSimilar(links)) return false;
            
            // Bỏ qua các phần tử có các từ khoá rác
            const textContent = element.textContent.toLowerCase();
            const junkKeywords = ['login', 'register', 'sign up', 'subscribe', 'newsletter', 'cookie', 'policy', 
                                'terms', 'conditions', 'copyright', 'all rights reserved'];
            
            if (junkKeywords.some(keyword => textContent.includes(keyword)) && textContent.length < 100) return false;
            
            return true;
        });
    }

    /**
     * Kiểm tra xem một phần tử có phải là navigation menu không
     */
    function isNavigationMenu(element) {
        // Kiểm tra class/id
        const elementText = (element.className + ' ' + element.id).toLowerCase();
        const navTerms = ['nav', 'menu', 'topbar', 'navbar', 'navigation', 'header', 'footer'];
        
        if (navTerms.some(term => elementText.includes(term))) {
            return true;
        }
        
        // Kiểm tra cấu trúc - navigation thường có nhiều link ngắn cùng cấp
        const links = element.querySelectorAll('a');
        if (links.length >= 4) {
            // Đếm số lượng link ngắn
            const shortLinks = Array.from(links).filter(link => 
                link.textContent.trim().length < 20
            );
            
            // Nếu hầu hết link đều ngắn
            if (shortLinks.length > links.length * 0.7) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Kiểm tra xem tất cả các link có giống nhau không
     */
    function areAllLinksSimilar(links) {
        if (links.length < 3) return false;
        
        // Lấy độ dài trung bình của các link
        const lengths = Array.from(links).map(link => link.textContent.trim().length);
        const avgLength = lengths.reduce((sum, len) => sum + len, 0) / lengths.length;
        
        // Tính độ lệch chuẩn
        const variance = lengths.reduce((sum, len) => sum + Math.pow(len - avgLength, 2), 0) / lengths.length;
        const stdDev = Math.sqrt(variance);
        
        // Nếu độ lệch chuẩn nhỏ, các link có độ dài tương tự nhau
        return stdDev < 5;
    }

    /**
     * Tính điểm đầy đủ thông tin cho mỗi mục
     */
    function getEntryCompletionScore(content) {
        let score = 0;
        
        // Điểm cho tiêu đề
        if (content.title) {
            score += Math.min(content.title.length / 10, 3); // Max 3 điểm
        }
        
        // Điểm cho link
        if (content.link) {
            score += 2;
            // Điểm cao hơn cho link có dạng article/post
            if (content.link.includes('/p/') || content.link.includes('/post/') || 
                content.link.includes('/article/') || content.link.includes('/news/')) {
                score += 1;
            }
        }
        
        // Điểm cho description
        if (content.description && content.description.length > 20) {
            score += Math.min(content.description.length / 50, 2); // Max 2 điểm
        }
        
        // Điểm cho hình ảnh
        if (content.imageUrl) {
            score += 2;
        }
        
        // Điểm cho ngày tháng
        if (content.date) {
            score += 1;
        }
        
        return score;
    }
});
