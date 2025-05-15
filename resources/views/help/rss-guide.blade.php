@extends('layouts.app')

@section('styles')
<style>
    .guide-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .step-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .step-header {
        background-color: #ff7846;
        color: white;
        padding: 15px 20px;
        font-weight: 600;
    }

    .step-body {
        padding: 25px;
    }

    .step-image {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 100%;
        margin: 15px 0;
    }

    .tip-box {
        background-color: rgba(255, 120, 70, 0.1);
        border-left: 4px solid #ff7846;
        padding: 15px;
        border-radius: 4px;
        margin: 15px 0;
    }

    .numbered-list {
        counter-reset: steps;
        list-style-type: none;
        padding-left: 0;
    }

    .numbered-list li {
        position: relative;
        padding-left: 50px;
        margin-bottom: 20px;
        counter-increment: steps;
    }

    .numbered-list li::before {
        content: counter(steps);
        position: absolute;
        left: 0;
        top: 0;
        width: 36px;
        height: 36px;
        background-color: #ff7846;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .guide-section {
        margin-bottom: 40px;
    }

    .guide-section h3 {
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .faq-item {
        margin-bottom: 20px;
    }

    .faq-question {
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }

    .faq-answer {
        color: #666;
    }

    .code-box {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        font-family: monospace;
        margin: 15px 0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            @include('partials.sidebar')
        </div>

        <div class="col-md-9 guide-container">
            <h1 class="mb-4">RSS Feed Generator Guide</h1>
            <p class="lead mb-5">Learn how to create RSS feeds from any website using our powerful RSS feed generator tool.</p>

            <div class="guide-section">
                <h3>What is an RSS Feed?</h3>
                <p>RSS (Really Simple Syndication) is a web feed format that allows users to access updates from websites in a standardized, computer-readable format. RSS feeds enable you to stay updated with your favorite websites without having to visit each site individually.</p>

                <p>Our RSS Feed Generator tool allows you to create an RSS feed from virtually any website, even if the site doesn't have a built-in feed. This is perfect for following websites that don't offer native RSS support.</p>
            </div>

            <div class="guide-section">
                <h3>Creating an RSS Feed in 3 Steps</h3>
                <p>Our tool makes it easy to create an RSS feed from any website in just three simple steps:</p>

                <div class="step-card">
                    <div class="step-header">Step 1: Enter the Website URL</div>
                    <div class="step-body">
                        <p>Start by entering the full URL of the website you want to create an RSS feed for. Make sure to include the http:// or https:// prefix.</p>

                        <div class="tip-box">
                            <strong>Tip:</strong> For best results, use the specific section of the website that contains the content you want in your feed. For example, use the blog section URL instead of the main website URL.
                        </div>

                        <p>After entering the URL, click the "Load Website" button. Our system will load a preview of the website for you to interact with.</p>

                        <img src="{{ asset('images/guide/step1.png') }}" alt="Step 1: Enter URL" class="step-image" onerror="this.style.display='none'">
                    </div>
                </div>

                <div class="step-card">
                    <div class="step-header">Step 2: Select Content Elements</div>
                    <div class="step-body">
                        <p>Once the website preview loads, you can visually select the elements you want to include in your RSS feed:</p>

                        <ol class="numbered-list">
                            <li>
                                <strong>Click on an article or post element</strong> in the preview. This could be a news article, blog post title, or any content item you want to include in your feed.
                            </li>
                            <li>
                                <strong>Our system will highlight similar elements</strong> on the page and generate a CSS selector that captures these elements.
                            </li>
                            <li>
                                <strong>Review the matching entries</strong> in the right panel to confirm that the correct content elements are selected.
                            </li>
                            <li>
                                <strong>Adjust the selector</strong> if needed by typing directly in the CSS selector field.
                            </li>
                        </ol>

                        <div class="tip-box">
                            <strong>Tip:</strong> Look for repeating elements like article titles, news items, or post containers. The best selectors are those that capture multiple similar items on the page.
                        </div>

                        <img src="{{ asset('images/guide/step2.png') }}" alt="Step 2: Select Elements" class="step-image" onerror="this.style.display='none'">
                    </div>
                </div>

                <div class="step-card">
                    <div class="step-header">Step 3: Configure and Generate the Feed</div>
                    <div class="step-body">
                        <p>After selecting the content elements, you can configure your RSS feed:</p>

                        <ol class="numbered-list">
                            <li>
                                <strong>Enter a title for your feed</strong> - This will be displayed in RSS readers.
                            </li>
                            <li>
                                <strong>Add a description</strong> (optional) - Provide more information about what's in your feed.
                            </li>
                            <li>
                                <strong>Set update frequency</strong> - Choose how often our system should check for new content.
                            </li>
                            <li>
                                <strong>Configure additional options</strong> - Set item limits, choose whether to include full content and images, etc.
                            </li>
                            <li>
                                <strong>Click "Generate"</strong> - Create your RSS feed.
                            </li>
                        </ol>

                        <p>Once generated, your feed will be available at a unique URL that you can use in any RSS reader.</p>

                        <img src="{{ asset('images/guide/step3.png') }}" alt="Step 3: Configure Feed" class="step-image" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>

            <div class="guide-section">
                <h3>Advanced CSS Selectors</h3>
                <p>For more precise control over what elements are included in your feed, you can use custom CSS selectors. Here are some useful selector examples:</p>

                <div class="code-box">
                    article           <!-- Selects all article elements -->
                    .post             <!-- Selects elements with class "post" -->
                    .news-item        <!-- Selects elements with class "news-item" -->
                    #content .entry   <!-- Selects elements with class "entry" inside an element with id "content" -->
                    h2.title          <!-- Selects h2 elements with class "title" -->
                    .blog article     <!-- Selects article elements inside elements with class "blog" -->
                </div>

                <p>If you're familiar with CSS selectors, you can craft very specific selectors to target exactly the content you want in your feed.</p>
            </div>

            <div class="guide-section">
                <h3>Frequently Asked Questions</h3>

                <div class="faq-item">
                    <div class="faq-question">Why doesn't the website load in the preview?</div>
                    <div class="faq-answer">
                        Some websites implement security measures that prevent them from being loaded in an iframe. In these cases, you may need to open the website in a new tab and use example selectors instead.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">How often is my RSS feed updated?</div>
                    <div class="faq-answer">
                        Your feed is updated according to the frequency you select during configuration. Options range from 30 minutes to 24 hours. Each time your feed is requested, we check if it's time to update the content.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Can I create feeds for dynamic websites with JavaScript content?</div>
                    <div class="faq-answer">
                        Yes! Our tool includes the option to render JavaScript when loading the website. Toggle the "Render JavaScript" option to enable this feature if the website uses JavaScript to load content.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">What if the website structure changes?</div>
                    <div class="faq-answer">
                        If the structure of the website changes significantly, your CSS selector might stop working. In this case, you'll need to create a new feed with updated selectors. For websites that change frequently, we recommend checking your feed regularly.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Is there a limit to how many RSS feeds I can create?</div>
                    <div class="faq-answer">
                        There is no strict limit on the number of feeds you can create, but we encourage responsible use of the system. Creating too many feeds with very frequent updates may impact performance.
                    </div>
                </div>
            </div>

            <div class="guide-section">
                <h3>Troubleshooting</h3>

                <div class="faq-item">
                    <div class="faq-question">No content is being selected when I click elements</div>
                    <div class="faq-answer">
                        Try clicking on different parts of the content, such as headings or container elements. Sometimes the clickable area might not be where you expect. You can also try entering a CSS selector manually.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">My feed is empty or not updating</div>
                    <div class="faq-answer">
                        Check if your CSS selector is still valid by recreating the feed. The website structure might have changed. Also, ensure the website itself has been updated with new content since your last feed update.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">The feed is missing images or content</div>
                    <div class="faq-answer">
                        Make sure you have enabled the "Include images" option during feed configuration. For full content, enable the "Full text extraction" option. Note that some websites may limit the content available for extraction.
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 mb-5">
                <a href="{{ route('web-scraper.index') }}" class="btn btn-primary">Start Creating Your RSS Feed</a>
            </div>
        </div>
    </div>
</div>
@endsection
