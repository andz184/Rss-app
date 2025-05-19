<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentTaskController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\TestLoginController;
use App\Http\Controllers\Auth\TestRegisterController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Test login routes
Route::get('/test-login', [TestLoginController::class, 'showLoginForm'])->name('test.login');
Route::post('/test-login', [TestLoginController::class, 'login'])->name('test.login.submit');

// Test registration routes
Route::get('/test-register', [TestRegisterController::class, 'showRegistrationForm'])->name('test.register');
Route::post('/test-register', [TestRegisterController::class, 'register'])->name('test.register.submit');

// Custom route for fixed feed ID 9
Route::get('/rss/9', function() {
    $filePath = public_path('feeds/scraped/rss9_fixed.xml');
    return response(file_get_contents($filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.fixed.9');


// Custom route for reference feed
Route::get('/rss/reference', function() {
    $filePath = public_path('feeds/scraped/custom_feed.xml');
    return response(file_get_contents($filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.reference');


// Custom route for test feed
Route::get('/rss/test', function() {
    $filePath = public_path('feeds/scraped/test_feed.xml');
    return response(file_get_contents($filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.test');

// RSS Feed JSON routes (public API endpoints)
Route::get('/rss/{feed}', [App\Http\Controllers\WebScraperController::class, 'showRssFeed'])->name('rss.show');
Route::get('/feeds/scraped/{feed}', [App\Http\Controllers\WebScraperController::class, 'serveScrapedFeed'])->name('web-scraper.serve');

// New JSON RSS Feed routes
Route::get('/json/rss/{feed}', [App\Http\Controllers\JsonRssFeedController::class, 'serve'])->name('json.rss.show');
Route::get('/json/feeds/scraped/{feed}', [App\Http\Controllers\JsonRssFeedController::class, 'fetchAndServe'])->name('json.web-scraper.serve');
Route::post('/json/convert', [App\Http\Controllers\JsonRssFeedController::class, 'convertXmlToJson'])->name('json.convert');
Route::get('/json/custom-feed', [App\Http\Controllers\JsonRssFeedController::class, 'customFeed'])->name('json.custom-feed');

Auth::routes();

Route::middleware('auth')->group(function () {
    // Home dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::post('categories/order', [CategoryController::class, 'updateOrder'])->name('categories.order');

    // Feeds
    Route::resource('feeds', FeedController::class);
    Route::post('feeds/{feed}/refresh', [FeedController::class, 'refresh'])->name('feeds.refresh');
    Route::post('feeds/{feed}/mark-all-read', [FeedController::class, 'markAllRead'])->name('feeds.mark-all-read');

    // Articles
    Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::post('articles/{article}/toggle-read', [ArticleController::class, 'toggleRead'])->name('articles.toggle-read');
    Route::post('articles/{article}/toggle-favorite', [ArticleController::class, 'toggleFavorite'])->name('articles.toggle-favorite');
    Route::post('articles/mark-all-read', [ArticleController::class, 'markAllRead'])->name('articles.mark-all-read');

    // Agents
    Route::resource('agents', AgentController::class);
    Route::post('agents/{agent}/toggle-active', [AgentController::class, 'toggleActive'])->name('agents.toggle-active');

    // Agent Tasks
    Route::resource('agent-tasks', AgentTaskController::class)->parameters([
        'agent-tasks' => 'agentTask'
    ])->except(['edit', 'update']);
    Route::post('agent-tasks/{agentTask}/cancel', [AgentTaskController::class, 'cancel'])->name('agent-tasks.cancel');

    // Web Scraper Routes
    Route::get('web-scraper', [App\Http\Controllers\WebScraperController::class, 'index'])->name('web-scraper.index');
    Route::post('web-scraper/fetch', [App\Http\Controllers\WebScraperController::class, 'fetchWebpage'])->name('web-scraper.fetch');
    Route::post('web-scraper/generate', [App\Http\Controllers\WebScraperController::class, 'generateRss'])->name('web-scraper.generate');
    Route::get('web-scraper/proxy', [App\Http\Controllers\WebScraperController::class, 'proxyWebpage'])->name('web-scraper.proxy');
    Route::get('help/rss-guide', [App\Http\Controllers\WebScraperController::class, 'rssGuide'])->name('help.rss-guide');

    // Popup Selector Routes
    Route::get('web-scraper/popup', [App\Http\Controllers\WebScraperController::class, 'showPopupSelector'])->name('web-scraper.popup');
    Route::post('web-scraper/fetch-html', [App\Http\Controllers\WebScraperController::class, 'fetchHtmlContent'])->name('web-scraper.fetch-html');
    Route::post('web-scraper/extract-elements', [App\Http\Controllers\WebScraperController::class, 'extractElements'])->name('web-scraper.extract-elements');
    Route::post('web-scraper/generate-popup', [App\Http\Controllers\WebScraperController::class, 'generateFromPopup'])->name('web-scraper.generate-popup');

    // New RSS Creator Routes
    Route::get('web-scraper/rss-creator', [App\Http\Controllers\WebScraperController::class, 'showRssCreator'])->name('web-scraper.rss-creator');
    Route::post('web-scraper/generate-rss', [App\Http\Controllers\WebScraperController::class, 'generateRssFeed'])->name('web-scraper.generate-rss');

    // RSS Feed preview
    Route::get('/rss/{feed}/preview', [App\Http\Controllers\WebScraperController::class, 'previewRssFeed'])->name('rss.preview');
});

// Route kiểm tra Python
Route::get('/kiem-tra-python', function () {
    return File::get(base_path('kiem_tra_python.php'));
});

// Routes cho chức năng chụp ảnh màn hình
Route::get('/screenshots', 'App\Http\Controllers\ScreenshotController@index')->name('screenshots.index');
Route::post('/screenshots/create', 'App\Http\Controllers\ScreenshotController@create')->name('screenshots.create');
Route::get('/screenshots/{filename}', 'App\Http\Controllers\ScreenshotController@show')->name('screenshots.show');

// Thêm route để kiểm tra Python chi tiết
Route::get('/check-python-env', function () {
    $output = "";
    $checkPython = shell_exec('python python/check_env.py 2>&1');
    if (!empty($checkPython)) {
        $output .= "<pre>" . htmlspecialchars($checkPython) . "</pre>";
    } else {
        $py_check = shell_exec('py python/check_env.py 2>&1');
        if (!empty($py_check)) {
            $output .= "<pre>" . htmlspecialchars($py_check) . "</pre>";
        } else {
            $output .= "<div class='alert alert-warning'>Không thể thực thi Python. Vui lòng cài đặt Python theo hướng dẫn.</div>";
            $output .= "<a href='/kiem-tra-python' class='btn btn-primary'>Xem hướng dẫn cài đặt Python</a>";
        }
    }
    return "<h1>Kiểm tra môi trường Python</h1>" . $output;
});

// Hiển thị ảnh chụp màn hình
Route::get('/screenshots-gallery', function () {
    return File::get(base_path('display_screenshot.php'));
});

// Custom route for simple feed example with JSON format
Route::get('/rss/simple', function() {
    $filePath = public_path('feeds/scraped/simple_feed.xml');
    try {
        $xmlContent = file_get_contents($filePath);
        $xml = simplexml_load_string($xmlContent);

        if ($xml && isset($xml->channel)) {
            $channel = $xml->channel;
            $feedData = [
                'title' => (string)$channel->title,
                'link' => (string)$channel->link,
                'description' => (string)$channel->description,
                'language' => (string)$channel->language,
                'lastBuildDate' => (string)$channel->lastBuildDate,
                'items' => []
            ];

            if (isset($channel->item)) {
                foreach ($channel->item as $item) {
                    $feedData['items'][] = [
                        'title' => (string)$item->title,
                        'link' => (string)$item->link,
                        'description' => (string)$item->description,
                        'pubDate' => (string)$item->pubDate,
                        'guid' => (string)$item->guid
                    ];
                }
            }

            return response()->json($feedData);
        } else {
            return response()->json(['error' => 'Invalid XML format'], 500);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to process feed: ' . $e->getMessage()], 500);
    }
})->name('rss.simple');

// Include custom feed routes if file exists
if (file_exists(base_path('routes/custom_feed.php'))) {
    require base_path('routes/custom_feed.php');
}

// Direct route to serve the exact JSON format provided by the user
Route::get('/json/ai-news-exact', function() {
    // Define the exact feed data structure as provided by the user
    $feedData = [
      "title" => "RSS Feed - www.artificialintelligence-news.com",
      "link" => "https://www.artificialintelligence-news.com/",
      "description" => "Generated RSS feed from https://www.artificialintelligence-news.com/",
      "language" => "vi",
      "lastBuildDate" => "Mon, 19 May 2025 01:08:41 +0000",
      "items" => [
        [
          "title" => "The role of machine learning in enhancing cloud-native container security",
          "link" => "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/the-role-of-machine-learning-in-enhancing-cloud-native-container-security/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Innovative machine learning uses transforming business applications",
          "link" => "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/innovative-machine-learning-uses-transforming-business-applications/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "AI and bots allegedly used to fraudulently boost music streams",
          "link" => "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/ai-and-bots-allegedly-used-to-fraudulently-boost-music-streams/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Best data security platforms of 2025",
          "link" => "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/best-data-security-platforms-of-2025/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "AI tool speeds up government feedback, experts urge caution",
          "link" => "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/ai-tool-speeds-up-government-feedback-experts-urge-caution/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Alibaba Wan2.1-VACE: Open-source AI video tool for all",
          "link" => "https://www.artificialintelligence-news.com/news/alibaba-wan2-1-vace-open-source-ai-video-tool-for-all/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/alibaba-wan2-1-vace-open-source-ai-video-tool-for-all/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Apple developing custom chips for smart glasses and more",
          "link" => "https://www.artificialintelligence-news.com/news/coming-soon-apple-is-developing-custom-chips-for-smart-glasses-and-more/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/coming-soon-apple-is-developing-custom-chips-for-smart-glasses-and-more/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Will the AI boom fuel a global energy crisis?",
          "link" => "https://www.artificialintelligence-news.com/news/will-the-ai-boom-fuel-a-global-energy-crisis/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/will-the-ai-boom-fuel-a-global-energy-crisis/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Can the US really enforce a global AI chip ban?",
          "link" => "https://www.artificialintelligence-news.com/news/can-the-us-really-enforce-a-global-ai-chip-ban/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/can-the-us-really-enforce-a-global-ai-chip-ban/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ],
        [
          "title" => "Congress pushes GPS tracking for every exported semiconductor",
          "link" => "https://www.artificialintelligence-news.com/news/congress-pushes-gps-tracking-for-every-exported-semiconductor/",
          "description" => "No description available.",
          "guid" => "https://www.artificialintelligence-news.com/news/congress-pushes-gps-tracking-for-every-exported-semiconductor/",
          "pubDate" => "Mon, 19 May 2025 01:08:41 +0000"
        ]
      ]
    ];

    return response()->json($feedData);
});

// Route to serve the generated JSON feed
Route::get('/json/ai-news-generated', function() {
    $filePath = public_path('feeds/scraped/ai_news_feed.json');
    if (file_exists($filePath)) {
        return response()->json(json_decode(file_get_contents($filePath), true));
    } else {
        return response()->json(['error' => 'Feed file not found'], 404);
    }
});

// Route to serve the dynamic aggregated feed
Route::get('/json/latest-feeds', function() {
    $filePath = public_path('feeds/scraped/latest_feed.json');
    if (file_exists($filePath)) {
        return response()->json(json_decode(file_get_contents($filePath), true));
    } else {
        return response()->json(['error' => 'Feed file not found'], 404);
    }
});

// Route to serve the fixed format feed (with consistent dates)
Route::get('/json/latest-feeds-fixed', function() {
    $filePath = public_path('feeds/scraped/latest_feed_fixed.json');
    if (file_exists($filePath)) {
        return response()->json(json_decode(file_get_contents($filePath), true));
    } else {
        return response()->json(['error' => 'Feed file not found'], 404);
    }
});
