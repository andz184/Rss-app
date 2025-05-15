#!/usr/bin/env python
import sys
import os
import time
import webbrowser
from datetime import datetime

# Thêm debug mode
DEBUG = True

def log_debug(message):
    """Ghi log để debug"""
    if not DEBUG:
        return

    log_file = "python/agent_debug.log"
    with open(log_file, "a", encoding="utf-8") as f:
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        f.write(f"[{timestamp}] {message}\n")
    print(f"DEBUG: {message}")

try:
    # Log thông tin
    log_debug(f"test_agent.py được gọi với tham số: {sys.argv}")
    log_debug(f"Python version: {sys.version}")
    log_debug(f"Thư mục hiện tại: {os.getcwd()}")

    # Đảm bảo thư mục tồn tại
    screenshots_dir = "screenshots"
    if not os.path.exists(screenshots_dir):
        os.makedirs(screenshots_dir)
        log_debug(f"Đã tạo thư mục {screenshots_dir}")
    else:
        log_debug(f"Thư mục {screenshots_dir} đã tồn tại")

    # Mở YouTube
    log_debug("Đang mở YouTube...")
    print("Đang mở YouTube...")
    webbrowser.open("https://www.youtube.com")

    # Đợi trang web mở
    log_debug("Đợi 5 giây để web load...")
    print("Đợi 5 giây để web load...")
    time.sleep(5)

    # Chụp màn hình
    try:
        from PIL import ImageGrab
        log_debug("Đã import PIL.ImageGrab thành công")
        print("Đang chụp màn hình...")
        screenshot = ImageGrab.grab()
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        screenshot_path = os.path.join(screenshots_dir, f"youtube_{timestamp}.png")
        screenshot.save(screenshot_path)
        log_debug(f"Đã lưu ảnh tại: {os.path.abspath(screenshot_path)}")
        print(f"Đã lưu ảnh tại: {os.path.abspath(screenshot_path)}")
    except Exception as e:
        error_msg = f"Lỗi khi chụp màn hình: {str(e)}"
        log_debug(error_msg)
        print(error_msg)

    log_debug("Hoàn thành!")
    print("Hoàn thành!")
except Exception as e:
    error_msg = f"Lỗi: {str(e)}"
    # Ghi vào file log
    with open("python/agent_error.log", "a", encoding="utf-8") as f:
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        f.write(f"[{timestamp}] {error_msg}\n")
    print(error_msg)
