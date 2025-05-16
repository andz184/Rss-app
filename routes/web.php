<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentTaskController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
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


// Custom route for fixed feed ID 9
Route::get('/rss/9', function() {
    $filePath = public_path('feeds/scraped/rss9_fixed.xml');
    return response(file_get_contents($filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.fixed.9');

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

    // Route to serve scraped RSS feeds
    Route::get('/feeds/scraped/{feed}', [App\Http\Controllers\WebScraperController::class, 'serveScrapedFeed'])->name('web-scraper.serve');

    // RSS Feed preview and XML routes
    Route::get('/rss/{feed}', [App\Http\Controllers\WebScraperController::class, 'showRssFeed'])->name('rss.show');
    Route::get('/rss/{feed}/preview', [App\Http\Controllers\WebScraperController::class, 'previewRssFeed'])->name('rss.preview');
});


// Custom route for fixed feed ID 9
Route::get('/rss/9', function() {
    $filePath = public_path('feeds/scraped/rss9_fixed.xml');
    return response(file_get_contents($filePath), 200)
        ->header('Content-Type', 'application/rss+xml; charset=utf-8');
})->name('rss.fixed.9');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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
