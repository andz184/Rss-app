@echo off
cd /d C:\xampp\htdocs\Rss\Rss-app
php update_rss.php >> rss_update_log.txt 2>&1
