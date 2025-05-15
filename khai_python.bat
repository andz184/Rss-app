@echo off
echo =======================================================
echo          KICH HOAT PYTHON CHO AGENT-S
echo =======================================================
echo.

REM Kiểm tra Python trong hệ thống
echo Dang kiem tra Python...
where python >nul 2>nul
if %errorlevel% equ 0 (
    echo Python da duoc cai dat:
    python --version
) else (
    echo Khong tim thay Python, kiem tra duong dan C:\Python311...
    if exist "C:\Python311\python.exe" (
        echo Tim thay Python tai C:\Python311!

        REM Thêm Python vào PATH
        echo Dang them Python vao PATH...
        setx PATH "%PATH%;C:\Python311;C:\Python311\Scripts" /M

        echo Python da duoc them vao PATH!
    ) else (
        echo Khong tim thay Python. Vui long chay cai_dat_python_auto.bat de cai dat.
        exit /b 1
    )
)

REM Kiểm tra Pillow
echo.
echo Dang kiem tra Pillow...
python -c "try: from PIL import ImageGrab; print('Pillow da duoc cai dat!'); print('Tat ca da san sang!') except ImportError: print('Pillow chua duoc cai dat.'); print('Dang cai dat Pillow...'); import subprocess; subprocess.call(['pip', 'install', 'Pillow'])"

echo.
echo =======================================================
echo    Hay khoi dong lai ung dung Laravel cua ban!
echo =======================================================
echo.
pause
