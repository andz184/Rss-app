@echo off
ECHO Starting Laravel and Python AI servers...

REM Set the title for the main window
TITLE Rss-app Launcher

ECHO Starting Laravel Development Server (http://localhost:8000)
START "Laravel Dev Server" cmd /c "php artisan serve --host=localhost --port=8000"

ECHO Starting Python AI Assistant Server (http://localhost:5000)
START "Python AI Server" cmd /c "cd steaming_ai && .\\venv\\Scripts\\activate && python app.py && pause"

ECHO Both servers have been launched in new windows.
ECHO You can now access your Laravel app at http://localhost:8000
