# Script để tải và cài đặt Python 3.11 tự động
$pythonUrl = "https://www.python.org/ftp/python/3.11.8/python-3.11.8-amd64.exe"
$pythonInstaller = "$env:TEMP\python-installer.exe"

Write-Host "Đang tải Python 3.11..." -ForegroundColor Green
Invoke-WebRequest -Uri $pythonUrl -OutFile $pythonInstaller

Write-Host "Đang cài đặt Python 3.11..." -ForegroundColor Green
# Cài đặt Python với các tùy chọn:
# /quiet: không hiển thị giao diện
# PrependPath=1: thêm Python vào PATH
# Include_pip=1: cài đặt pip
Start-Process -FilePath $pythonInstaller -ArgumentList "/quiet PrependPath=1 Include_pip=1" -Wait

Write-Host "Đang kiểm tra cài đặt Python..." -ForegroundColor Green
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Kiểm tra xem Python đã được cài đặt thành công chưa
python --version

if ($LASTEXITCODE -eq 0) {
    Write-Host "Python đã được cài đặt thành công!" -ForegroundColor Green
    Write-Host "Đang cài đặt thư viện Pillow..." -ForegroundColor Green
    python -m pip install Pillow

    if ($LASTEXITCODE -eq 0) {
        Write-Host "Pillow đã được cài đặt thành công!" -ForegroundColor Green
    } else {
        Write-Host "Không thể cài đặt Pillow." -ForegroundColor Red
    }
} else {
    Write-Host "Cài đặt Python không thành công. Vui lòng cài đặt thủ công từ python.org" -ForegroundColor Red
}

Write-Host "Quá trình cài đặt đã hoàn tất." -ForegroundColor Green
Write-Host "Nhấn phím bất kỳ để đóng cửa sổ này..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
