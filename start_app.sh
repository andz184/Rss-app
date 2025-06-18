#!/bin/bash
# =================================================================
# WARNING: This script is for basic testing/development ONLY.
# It is NOT suitable for a real production environment because:
#   - It uses development servers which are slow and insecure.
#   - It does not manage processes (they won't restart if they crash).
# For production, use the Nginx + Supervisor method I described earlier.
# =================================================================

echo "Starting Laravel and Python servers in the background..."

# --- Start Laravel Server ---
# nohup ensures the process keeps running after you log out.
# '&' runs the process in the background.
# Output is redirected to a log file.
echo "Starting Laravel server... Logs will be in laravel.log"
nohup php artisan serve --host=0.0.0.0 --port=8000 > laravel.log 2>&1 &

# --- Start Python AI Server ---
echo "Starting Python server... Logs will be in python.log"
cd steaming_ai
source venv/bin/activate
nohup python app.py > ../python.log 2>&1 &
cd ..

echo ""
echo "Servers have been launched."
echo "You can now access your app at http://<your_vps_ip>:8000"
echo ""
echo "To stop the servers, run the 'stop_app.sh' script."
