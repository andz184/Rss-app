@echo off
echo Updating RSS feeds...
php update_rss.php
echo.
echo Generating custom AI news feed...
php generate_custom_feed.php
echo.
echo Generating JSON feed...
php generate_json_feed.php
echo.
echo All feeds updated successfully.
pause
