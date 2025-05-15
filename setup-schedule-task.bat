@echo off
echo ===================================================================
echo        THIET LAP TU DONG CAP NHAT RSS CHO WINDOWS
echo ===================================================================
echo.

REM Lấy đường dẫn hiện tại
set CURRENT_DIR=%~dp0
set CURRENT_DIR=%CURRENT_DIR:~0,-1%

REM Đường dẫn đến PHP và Artisan
set PHP_PATH="%~dp0..\..\php\php.exe"
set ARTISAN_PATH="%CURRENT_DIR%\artisan"

echo Thiet lap lich chay voi Task Scheduler...
echo.
echo Se tao mot task chay moi phut de kiem tra lich trinh Laravel
echo Laravel se chay cap nhat RSS moi 12 gio (vao 00:00 va 12:00)
echo.

REM Tạo Task Scheduler để chạy mỗi phút
schtasks /create /sc minute /mo 1 /tn "Laravel RSS Reader Schedule" /tr "%PHP_PATH% %ARTISAN_PATH% schedule:run" /f

if %ERRORLEVEL% EQU 0 (
    echo Task da duoc tao thanh cong!
    echo.
    echo Ban co the xem va chinh sua task trong Task Scheduler.
) else (
    echo Co loi khi tao task!
    echo Ban co the tao thu cong trong Task Scheduler:
    echo - Mo Task Scheduler
    echo - Chon 'Create Basic Task'
    echo - Dat ten: 'Laravel RSS Reader Schedule'
    echo - Trigger: Daily, lap lai moi 1 phut
    echo - Action: Chay chuong trinh: %PHP_PATH%
    echo - Them arguments: %ARTISAN_PATH% schedule:run
    echo - Start in: %CURRENT_DIR%
)

echo.
echo De kiem tra ngay lap tuc, hay chay:
echo php artisan feeds:fetch
echo.
echo ===================================================================

pause
