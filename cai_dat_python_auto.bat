@echo off
echo =======================================================
echo      CAI DAT PYTHON CHO AGENT-S (TU DONG HOAN TOAN)
echo =======================================================
echo.
echo Dang tai Python 3.11.8...
echo.

REM Tạo thư mục tạm để tải file
mkdir "%TEMP%\python_setup" 2>nul
cd /d "%TEMP%\python_setup"

REM Tải Python từ trang chính thức
curl -L -o python-3.11.8-amd64.exe https://www.python.org/ftp/python/3.11.8/python-3.11.8-amd64.exe

echo.
echo Dang cai dat Python (tren thanh tien trinh)...
echo LUU Y: Qua trinh nay se mat vai phut.
echo.

REM Cài đặt Python (tự động, chọn cài đặt cho All Users, thêm vào PATH)
python-3.11.8-amd64.exe /quiet InstallAllUsers=1 PrependPath=1 Include_test=0 Include_pip=1

echo.
echo Cho Python duoc cai dat xong...
ping -n 10 127.0.0.1 > nul

echo.
echo Dang cai dat thu vien Pillow...
echo.

REM Cài đặt Pillow
"%PROGRAMFILES%\Python311\python.exe" -m pip install Pillow

echo.
echo Tao file kiem tra...
echo.

REM Tạo file Python tạm để kiểm tra
echo import sys > check_installation.py
echo import PIL >> check_installation.py
echo from PIL import Image, ImageGrab >> check_installation.py
echo print("Python version:", sys.version) >> check_installation.py
echo print("Pillow version:", PIL.__version__) >> check_installation.py
echo print("Test ImageGrab...") >> check_installation.py
echo test_img = ImageGrab.grab(bbox=(0, 0, 10, 10)) >> check_installation.py
echo print("Kiem tra thanh cong!") >> check_installation.py

REM Chạy file kiểm tra
echo Kiem tra cai dat:
echo.
"%PROGRAMFILES%\Python311\python.exe" check_installation.py

REM Quay lại thư mục gốc
cd /d "%~dp0"

echo.
echo =======================================================
echo Python da duoc cai dat, hay khoi dong lai ung dung!
echo =======================================================
echo.
pause
