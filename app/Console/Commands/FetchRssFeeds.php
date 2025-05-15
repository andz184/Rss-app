<?php

namespace App\Console\Commands;

use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:fetch {--feed_id= : Cập nhật một nguồn tin cụ thể}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật tất cả nguồn tin RSS đang hoạt động';

    /**
     * FeedService instance
     */
    protected FeedService $feedService;

    /**
     * Create a new command instance.
     */
    public function __construct(FeedService $feedService)
    {
        parent::__construct();
        $this->feedService = $feedService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu cập nhật nguồn tin RSS...');

        $feedId = $this->option('feed_id');

        if ($feedId) {
            // Cập nhật một nguồn tin cụ thể
            $feed = Feed::find($feedId);
            if (!$feed) {
                $this->error("Không tìm thấy nguồn tin với ID: {$feedId}");
                return 1;
            }

            $this->updateFeed($feed);
        } else {
            // Cập nhật tất cả nguồn tin đang hoạt động
            $feeds = Feed::where('is_active', true)->get();
            $this->info("Đã tìm thấy {$feeds->count()} nguồn tin đang hoạt động.");

            $progressBar = $this->output->createProgressBar($feeds->count());
            $progressBar->start();

            $totalNew = 0;
            $successCount = 0;
            $errorCount = 0;

            foreach ($feeds as $feed) {
                $result = $this->updateFeed($feed, false);

                if ($result['status'] === 'success') {
                    $successCount++;
                    $totalNew += $result['new_articles'];
                } else {
                    $errorCount++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("Tổng kết:");
            $this->info("- Nguồn tin được cập nhật thành công: {$successCount}");
            $this->info("- Nguồn tin gặp lỗi: {$errorCount}");
            $this->info("- Tổng số bài viết mới: {$totalNew}");
        }

        $this->info('Đã hoàn tất cập nhật nguồn tin RSS.');
        return 0;
    }

    /**
     * Cập nhật một nguồn tin cụ thể
     */
    protected function updateFeed(Feed $feed, bool $verbose = true): array
    {
        if ($verbose) {
            $this->info("Đang cập nhật nguồn tin '{$feed->title}'...");
        }

        try {
            $result = $this->feedService->fetchAndParse($feed);

            if ($verbose) {
                if ($result['status'] === 'success') {
                    $this->info("✓ Đã cập nhật thành công: {$result['new_articles']} bài viết mới.");
                } elseif ($result['status'] === 'not_modified') {
                    $this->line("• Nguồn tin không có thay đổi từ lần cập nhật trước.");
                } else {
                    $this->error("✗ Lỗi: {$result['message']}");
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Lỗi cập nhật nguồn tin ID #{$feed->id}: " . $e->getMessage());

            if ($verbose) {
                $this->error("✗ Đã xảy ra lỗi: " . $e->getMessage());
            }

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'new_articles' => 0
            ];
        }
    }
}
