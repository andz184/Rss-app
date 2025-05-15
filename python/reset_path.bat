@echo off
echo Dang thiet lap PATH cho Python...

REM Tạo symbolic link Python trong thư mục hiện tại
if exist "C:\Python311\python.exe" (
    echo Tao symbolic link Python...
    mklink "%~dp0python.exe" "C:\Python311\python.exe"

    REM Tạo file .env với thông tin Python
    echo PYTHON_PATH=C:\Python311\python.exe > "%~dp0.env"
) else if exist "C:\Program Files\Python311\python.exe" (
    echo Tao symbolic link Python...
    mklink "%~dp0python.exe" "C:\Program Files\Python311\python.exe"

    REM Tạo file .env với thông tin Python
    echo PYTHON_PATH=C:\Program Files\Python311\python.exe > "%~dp0.env"
) else (
    echo Khong tim thay Python tai C:\Python311 hoac C:\Program Files\Python311
    echo Vui long cai dat Python va chay lai script nay.
    exit /b 1
)

echo Dang tao file test_local_python.py...
echo import sys > "%~dp0test_local_python.py"
echo import os >> "%~dp0test_local_python.py"
echo print("Python version:", sys.version) >> "%~dp0test_local_python.py"
echo print("Python path:", sys.executable) >> "%~dp0test_local_python.py"
echo print("Current directory:", os.getcwd()) >> "%~dp0test_local_python.py"
echo print("SUCCESS - Python is working!") >> "%~dp0test_local_python.py"

echo Dang kiem tra Python...
"%~dp0python.exe" "%~dp0test_local_python.py"

echo.
echo Python da duoc cau hinh thanh cong!
echo.
pause
