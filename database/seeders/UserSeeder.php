<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Feed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'theme' => 'light',
            'display_mode' => 'compact',
            'articles_per_page' => 20,
        ]);

        // Create manager user
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'theme' => 'dark',
            'display_mode' => 'expanded',
            'articles_per_page' => 15,
        ]);

        // Create regular user
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'theme' => 'light',
            'display_mode' => 'compact',
            'articles_per_page' => 10,
        ]);

        // Create sample categories for admin
        $techCategory = Category::create([
            'name' => 'Technology',
            'color' => '#3498db',
            'user_id' => $adminUser->id,
            'order' => 1,
        ]);

        $newsCategory = Category::create([
            'name' => 'News',
            'color' => '#e74c3c',
            'user_id' => $adminUser->id,
            'order' => 2,
        ]);

        $sportsCategory = Category::create([
            'name' => 'Sports',
            'color' => '#2ecc71',
            'user_id' => $adminUser->id,
            'order' => 3,
        ]);

        // Create sample feeds for admin
        Feed::create([
            'title' => 'The Verge',
            'feed_url' => 'https://www.theverge.com/rss/index.xml',
            'site_url' => 'https://www.theverge.com',
            'description' => 'The Verge technology news and reviews',
            'category_id' => $techCategory->id,
            'user_id' => $adminUser->id,
        ]);

        Feed::create([
            'title' => 'TechCrunch',
            'feed_url' => 'https://techcrunch.com/feed/',
            'site_url' => 'https://techcrunch.com',
            'description' => 'Startup and Technology News',
            'category_id' => $techCategory->id,
            'user_id' => $adminUser->id,
        ]);

        Feed::create([
            'title' => 'CNN',
            'feed_url' => 'http://rss.cnn.com/rss/edition.rss',
            'site_url' => 'https://www.cnn.com',
            'description' => 'CNN - Breaking News, Latest News and Videos',
            'category_id' => $newsCategory->id,
            'user_id' => $adminUser->id,
        ]);

        Feed::create([
            'title' => 'ESPN',
            'feed_url' => 'https://www.espn.com/espn/rss/news',
            'site_url' => 'https://www.espn.com',
            'description' => 'Latest sports news from ESPN',
            'category_id' => $sportsCategory->id,
            'user_id' => $adminUser->id,
        ]);

        // Create sample categories for manager
        $financeCategory = Category::create([
            'name' => 'Finance',
            'color' => '#f39c12',
            'user_id' => $managerUser->id,
            'order' => 1,
        ]);

        $scienceCategory = Category::create([
            'name' => 'Science',
            'color' => '#9b59b6',
            'user_id' => $managerUser->id,
            'order' => 2,
        ]);

        // Create sample feeds for manager
        Feed::create([
            'title' => 'Bloomberg',
            'feed_url' => 'https://www.bloomberg.com/politics/feeds/site.xml',
            'site_url' => 'https://www.bloomberg.com',
            'description' => 'Bloomberg delivers business and markets news, data, analysis',
            'category_id' => $financeCategory->id,
            'user_id' => $managerUser->id,
        ]);

        Feed::create([
            'title' => 'Science Daily',
            'feed_url' => 'https://www.sciencedaily.com/rss/top.xml',
            'site_url' => 'https://www.sciencedaily.com',
            'description' => 'Breaking science news and articles on global warming',
            'category_id' => $scienceCategory->id,
            'user_id' => $managerUser->id,
        ]);
    }
}
