# RSS Reader User Guide

Welcome to the RSS Reader application! This guide will help you understand the features and functionality available to you.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Articles](#articles)
4. [Feeds](#feeds)
5. [Categories](#categories)
6. [Settings](#settings)
7. [Commands & Administration](#commands--administration)

## Getting Started

### Login

To access the RSS Reader, use one of the following accounts:

- **Admin User**:
  - Email: `admin@example.com`
  - Password: `password`

- **Manager User**:
  - Email: `manager@example.com`
  - Password: `password`

- **Regular User**:
  - Email: `user@example.com`
  - Password: `password`

### Interface Overview

The application features a modern, responsive interface with:

- **Sidebar**: Access different sections of the application (Articles, Feeds, Categories)
- **Header**: Search functionality, theme toggle (light/dark), and user profile menu
- **Main Content Area**: Displays the content of the selected section

## Dashboard

The dashboard provides an overview of your RSS feed activity, including:

- Recent articles
- Unread counts
- Feed statistics

## Articles

The Articles section is where you read content from your feeds.

### Article Views

- **All Articles**: Shows all articles from all feeds
- **Unread**: Shows only unread articles
- **Favorites**: Shows articles you've marked as favorites

### Filtering Articles

You can filter articles by:

- Category (click a category in the sidebar)
- Feed (click a feed in the sidebar)
- Read/Unread status
- Favorites

### Article Actions

When viewing an article list, you can:

- Click an article title to read the full content
- Mark articles as read/unread (envelope icon)
- Mark articles as favorite/unfavorite (star icon)
- Mark all articles as read (button in the top right)

When reading an article, you can:

- Toggle read/unread status
- Toggle favorite status
- Visit the original website
- Navigate to previous/next articles

## Feeds

The Feeds section allows you to manage your RSS feed subscriptions.

### Adding a Feed

1. Click **Add New Feed** button
2. Enter the feed URL (must be a valid RSS or Atom feed)
3. Select a category (optional)
4. Click **Save**

The system will automatically fetch the feed title, description, and initial articles.

### Managing Feeds

From the Feeds page, you can:

- View feed statistics (total articles, unread count)
- Refresh feeds manually (sync icon)
- Edit feed details (pencil icon)
- Delete feeds (trash icon)

### Feed Properties

When editing a feed, you can modify:

- Title
- Feed URL
- Category
- Active status (inactive feeds won't be automatically updated)

## Categories

The Categories section helps you organize your feeds.

### Adding a Category

1. Click **Add New Category** button
2. Enter a name for the category
3. Choose a color (used for visual identification)
4. Click **Save**

### Managing Categories

From the Categories page, you can:

- View feeds in each category
- Edit categories (name and color)
- Delete categories (this won't delete the feeds within the category)
- Reorder categories by dragging them

## Settings

The Settings section allows you to customize your experience.

### Theme

Toggle between light and dark themes using the moon/sun icon in the header.
Your theme preference is saved automatically.

### Display Preferences

You can configure:

- Articles per page
- Display mode (compact or expanded)

## Commands & Administration

### Command Line Tools

The application includes a command-line tool for fetching articles:

```bash
# Fetch articles from all active feeds
php artisan feeds:fetch

# Fetch articles from all feeds regardless of active status
php artisan feeds:fetch --all

# Fetch articles from a specific feed
php artisan feeds:fetch --feed=1
```

### Scheduled Tasks

For automatic updates, add the following to your server's crontab:

```
* * * * * cd /path-to-your-app && php artisan schedule:run >> /dev/null 2>&1
```

The application will automatically fetch new articles every hour by default.

## Troubleshooting

### Common Issues

1. **Empty feed list**: Make sure you've added feeds through the "Add New Feed" button
2. **No articles appearing**: Refresh your feeds manually, or run the fetch command
3. **Invalid feed URL**: Ensure the URL points directly to an RSS or Atom feed

### Support

If you encounter any issues, please contact support at support@example.com.

## Conclusion

You now have a powerful, personalized RSS reader at your fingertips! Start by adding your favorite feeds and organizing them into categories to create a custom news dashboard tailored to your interests. 
