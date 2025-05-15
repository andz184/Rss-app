# Laravel RSS Reader

A self-hosted RSS/Atom feed reader built with Laravel, inspired by [FreshRSS](https://github.com/FreshRSS/FreshRSS).

## Features

- Subscribe to RSS and Atom feeds
- Organize feeds into categories
- Mark articles as read/unread
- Star/favorite articles for later reference
- Automatic feed updates via scheduled tasks
- Clean, responsive user interface
- Multi-user support
- Dark/Light mode

## Requirements

- PHP 8.1 or higher
- Laravel 10.x
- MySQL, PostgreSQL, or SQLite database
- Composer

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/laravel-rss-reader.git
   cd laravel-rss-reader
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Copy the environment file:
   ```
   cp .env.example .env
   ```

4. Generate application key:
   ```
   php artisan key:generate
   ```

5. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_rss
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Run database migrations:
   ```
   php artisan migrate
   ```

7. Start the development server:
   ```
   php artisan serve
   ```

8. Set up the task scheduler in your crontab to automatically fetch feeds:
   ```
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

## Feed Updates

You can manually update all feeds with the following command:
```
php artisan feeds:fetch --all
```

Or update a specific feed by its ID:
```
php artisan feeds:fetch --feed=1
```

## Usage

1. Register a new account
2. Create categories to organize your feeds
3. Add new feeds by providing RSS/Atom URLs
4. Browse your feeds and read articles
5. Mark articles as read/unread or favorite them for later

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Credits

- Built with [Laravel](https://laravel.com/)
- Inspired by [FreshRSS](https://github.com/FreshRSS/FreshRSS)
