<?php

namespace App\Console\Commands;

use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:fetch {--feed=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch RSS feeds and update articles';

    /**
     * The feed service instance.
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
    public function handle(): int
    {
        $feedId = $this->option('feed');
        $all = $this->option('all');

        if (!$feedId && !$all) {
            $this->error('You must specify --feed=[id] or --all');
            return 1;
        }

        if ($feedId) {
            $feed = Feed::find($feedId);

            if (!$feed) {
                $this->error("Feed with ID {$feedId} not found");
                return 1;
            }

            $this->processFeed($feed);

            return 0;
        }

        // Process all active feeds
        $feeds = Feed::where('is_active', true)->get();
        $count = $feeds->count();

        $this->info("Processing {$count} feeds...");
        $progress = $this->output->createProgressBar($count);
        $progress->start();

        $successCount = 0;
        $errorCount = 0;
        $newArticles = 0;

        foreach ($feeds as $feed) {
            $result = $this->processFeed($feed, false);

            if ($result['status'] === 'success' || $result['status'] === 'not_modified') {
                $successCount++;
                $newArticles += $result['new_articles'] ?? 0;
            } else {
                $errorCount++;
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine();

        $this->info("Finished processing feeds:");
        $this->info("- Success: {$successCount}");
        $this->info("- Errors: {$errorCount}");
        $this->info("- New articles: {$newArticles}");

        return 0;
    }

    /**
     * Process a single feed.
     */
    protected function processFeed(Feed $feed, bool $showOutput = true): array
    {
        if ($showOutput) {
            $this->info("Processing feed: {$feed->title} ({$feed->feed_url})");
        }

        try {
            $result = $this->feedService->fetchAndParse($feed);

            if ($showOutput) {
                if ($result['status'] === 'success') {
                    $this->info("Success! Added {$result['new_articles']} new articles");
                } elseif ($result['status'] === 'not_modified') {
                    $this->info("Feed not modified since last check");
                } else {
                    $this->error("Error: {$result['message']}");
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Command error when processing feed: ' . $e->getMessage(), [
                'feed_id' => $feed->id,
            ]);

            if ($showOutput) {
                $this->error("Exception: {$e->getMessage()}");
            }

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
