#!/bin/bash
echo "Stopping servers..."

# Find and kill the process for 'php artisan serve'
pkill -f "php artisan serve"
echo "Laravel server stopped."

# Find and kill the process for 'python app.py'
pkill -f "python app.py"
echo "Python AI server stopped."

echo "All servers have been stopped."
