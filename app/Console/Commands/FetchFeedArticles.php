<?php

namespace App\Console\Commands;

use App\Models\Feed;
use App\Services\FeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchFeedArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:fetch {--feed=} {--all} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from RSS feeds';

    /**
     * Execute the console command.
     */
    public function handle(FeedService $feedService)
    {
        $startTime = now();
        $this->info('Starting feed fetch: ' . $startTime->toDateTimeString());

        // Determine which feeds to process
        if ($this->option('feed')) {
            $feeds = Feed::where('id', $this->option('feed'))->get();
            $this->info('Processing single feed ID: ' . $this->option('feed'));
        } elseif ($this->option('all')) {
            $feeds = Feed::all();
            $this->info('Processing all feeds: ' . $feeds->count() . ' total');
        } else {
            $feeds = Feed::where('is_active', true)->get();
            $this->info('Processing active feeds: ' . $feeds->count() . ' total');
        }

        if ($feeds->isEmpty()) {
            $this->warn('No feeds to process');
            return 0;
        }

        $successCount = 0;
        $errorCount = 0;
        $newArticlesTotal = 0;

        $progressBar = $this->output->createProgressBar($feeds->count());
        $progressBar->start();

        foreach ($feeds as $feed) {
            try {
                $this->line('');
                $this->line('Processing feed: ' . $feed->title . ' (' . $feed->feed_url . ')');

                $result = $feedService->fetchAndParse($feed);

                if ($result['status'] === 'success') {
                    $successCount++;
                    $newArticlesTotal += $result['new_articles'];
                    $this->info('  ✓ Success! ' . $result['new_articles'] . ' new articles');
                } elseif ($result['status'] === 'not_modified') {
                    $successCount++;
                    $this->info('  ✓ Feed not modified since last check');
                } else {
                    $errorCount++;
                    $this->error('  ✗ Error: ' . $result['message']);
                }

            } catch (\Exception $e) {
                $errorCount++;
                $this->error('  ✗ Exception: ' . $e->getMessage());
                Log::error('Feed fetch exception', [
                    'feed_id' => $feed->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $this->line('');

        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        $this->info('Feed fetch completed in ' . $duration . ' seconds');
        $this->info("Processed: {$feeds->count()} feeds, Success: {$successCount}, Errors: {$errorCount}, New Articles: {$newArticlesTotal}");

        return 0;
    }
}
