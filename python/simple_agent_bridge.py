#!/usr/bin/env python
import sys
import json
import os
import traceback
import base64
import subprocess
import webbrowser
import time
from datetime import datetime

# Đường dẫn này để debug
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
LOG_FILE = os.path.join(SCRIPT_DIR, "agent_debug.log")

def log_debug(message):
    """Ghi log vào file để debug"""
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        f.write(f"[{timestamp}] {message}\n")
    print(f"DEBUG: {message}")

# Log thông tin môi trường khi script được gọi
log_debug(f"Script được gọi tại: {SCRIPT_DIR}")
log_debug(f"Python version: {sys.version}")
log_debug(f"Tham số: {sys.argv}")
log_debug(f"OS: {os.name}")

# Thử import thư viện PIL nếu có
try:
    from PIL import ImageGrab
    HAS_PIL = True
    log_debug("Đã import thành công thư viện PIL")
except ImportError as e:
    HAS_PIL = False
    log_debug(f"Không thể import thư viện PIL: {str(e)}")

def take_screenshot():
    """Chụp màn hình và trả về dữ liệu hình ảnh"""
    if not HAS_PIL:
        log_debug("Không thể chụp màn hình vì PIL không được cài đặt")
        return None

    try:
        # Đảm bảo thư mục tồn tại
        screenshots_dir = os.path.join(SCRIPT_DIR, "screenshots")
        if not os.path.exists(screenshots_dir):
            os.makedirs(screenshots_dir)
            log_debug(f"Đã tạo thư mục screenshots: {screenshots_dir}")

        # Lấy ảnh chụp màn hình
        log_debug("Bắt đầu chụp màn hình...")
        screenshot = ImageGrab.grab()
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        screenshot_path = os.path.join(screenshots_dir, f"screen_{timestamp}.png")
        screenshot.save(screenshot_path)

        # In ra đường dẫn để debug
        log_debug(f"Đã lưu ảnh vào: {os.path.abspath(screenshot_path)}")

        # Chuyển đổi hình ảnh thành base64
        with open(screenshot_path, "rb") as img_file:
            img_data = base64.b64encode(img_file.read()).decode('utf-8')
            log_debug(f"Đã chuyển đổi ảnh thành base64 (kích thước: {len(img_data)} ký tự)")

        # Tạo action cho ảnh chụp màn hình
        return {
            "type": "screenshot",
            "data": {
                "path": screenshot_path,
                "width": screenshot.width,
                "height": screenshot.height,
                "image_base64": img_data
            }
        }
    except Exception as e:
        error_msg = f"Lỗi khi chụp màn hình: {str(e)}\n{traceback.format_exc()}"
        log_debug(error_msg)
        with open(os.path.join(SCRIPT_DIR, "agent_error_log.txt"), "a", encoding="utf-8") as f:
            f.write(error_msg)
        return None

def open_browser(url):
    """Mở trình duyệt với URL cụ thể"""
    try:
        webbrowser.open(url)
        return True
    except:
        return False

def open_application(app_name):
    """Mở ứng dụng dựa trên tên"""
    try:
        if os.name == 'nt':  # Windows
            if app_name.lower() in ['notepad', 'notepad.exe']:
                subprocess.Popen(['notepad.exe'])
            elif app_name.lower() in ['calc', 'calculator', 'calc.exe']:
                subprocess.Popen(['calc.exe'])
            elif app_name.lower() in ['explorer', 'file explorer', 'explorer.exe']:
                subprocess.Popen(['explorer.exe'])
            elif app_name.lower() in ['cmd', 'command prompt', 'cmd.exe']:
                subprocess.Popen(['cmd.exe'])
            elif app_name.lower() in ['chrome', 'google chrome']:
                subprocess.Popen(['C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'])
            elif app_name.lower() in ['edge', 'microsoft edge']:
                subprocess.Popen(['C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'])
            else:
                return False
            return True
        else:  # Linux/Mac
            return False
    except:
        return False

def process_instruction(instruction, agent_config):
    """
    Xử lý chỉ thị từ Laravel và trả về kết quả
    """
    try:
        # Ghi log
        with open("python/agent_log.txt", "a", encoding="utf-8") as f:
            f.write(f"Nhận chỉ thị: {instruction}\n")
            f.write(f"Cấu hình: {json.dumps(agent_config, ensure_ascii=False)}\n")

        # Chụp màn hình ban đầu
        screenshot_action = take_screenshot()
        actions = []

        instruction_lower = instruction.lower()

        # Phân tích yêu cầu
        if "mở" in instruction_lower or "chạy" in instruction_lower or "khởi động" in instruction_lower:
            # Mở các ứng dụng hoặc trang web

            # Website phổ biến
            if "github" in instruction_lower:
                open_browser("https://github.com")
                actions.append({
                    "type": "text",
                    "data": {"content": "Đã mở trang GitHub."}
                })
                actions.append({
                    "type": "browser",
                    "data": {
                        "action": "navigate",
                        "url": "https://github.com"
                    }
                })
            elif "youtube" in instruction_lower:
                open_browser("https://www.youtube.com")
                actions.append({
                    "type": "text",
                    "data": {"content": "Đã mở trang YouTube."}
                })
                actions.append({
                    "type": "browser",
                    "data": {
                        "action": "navigate",
                        "url": "https://www.youtube.com"
                    }
                })
            elif "google" in instruction_lower:
                open_browser("https://www.google.com")
                actions.append({
                    "type": "text",
                    "data": {"content": "Đã mở trang Google."}
                })
                actions.append({
                    "type": "browser",
                    "data": {
                        "action": "navigate",
                        "url": "https://www.google.com"
                    }
                })

            # Ứng dụng Windows
            elif any(app in instruction_lower for app in ["notepad", "ghi chú"]):
                if open_application("notepad"):
                    actions.append({
                        "type": "text",
                        "data": {"content": "Đã mở Notepad."}
                    })
                else:
                    actions.append({
                        "type": "text",
                        "data": {"content": "Không thể mở Notepad."}
                    })
            elif any(app in instruction_lower for app in ["calculator", "máy tính", "calc"]):
                if open_application("calc"):
                    actions.append({
                        "type": "text",
                        "data": {"content": "Đã mở Calculator."}
                    })
                else:
                    actions.append({
                        "type": "text",
                        "data": {"content": "Không thể mở Calculator."}
                    })
            elif any(app in instruction_lower for app in ["explorer", "file explorer", "quản lý tệp"]):
                if open_application("explorer"):
                    actions.append({
                        "type": "text",
                        "data": {"content": "Đã mở File Explorer."}
                    })
                else:
                    actions.append({
                        "type": "text",
                        "data": {"content": "Không thể mở File Explorer."}
                    })
            else:
                actions.append({
                    "type": "text",
                    "data": {"content": f"Không biết cách mở: {instruction}"}
                })

        elif "tìm" in instruction_lower or "search" in instruction_lower:
            # Tách từ khóa tìm kiếm
            search_terms = instruction_lower.split("tìm", 1)
            if len(search_terms) > 1:
                search_query = search_terms[1].strip()
                google_url = f"https://www.google.com/search?q={search_query}"
                open_browser(google_url)
                actions.append({
                    "type": "text",
                    "data": {"content": f"Đã tìm kiếm Google cho: {search_query}"}
                })
                actions.append({
                    "type": "browser",
                    "data": {
                        "action": "navigate",
                        "url": google_url
                    }
                })
            else:
                actions.append({
                    "type": "text",
                    "data": {"content": "Không hiểu từ khóa tìm kiếm."}
                })

        else:
            # Xử lý mặc định cho các chỉ thị khác
            actions.append({
                "type": "text",
                "data": {"content": f"Đã nhận lệnh: {instruction}"}
            })

        # Giữ cho kết quả đủ thực, đợi một chút
        time.sleep(1)

        # Chụp màn hình sau khi thực hiện hành động
        final_screenshot = take_screenshot()

        # Tạo kết quả
        result = {
            "success": True,
            "message": f"Đã xử lý: {instruction}",
            "actions": actions,
            "output": f"Tôi đã xử lý yêu cầu của bạn: '{instruction}'"
        }

        # Thêm ảnh chụp màn hình vào kết quả nếu có
        if screenshot_action:
            result["actions"].insert(0, screenshot_action)

        if final_screenshot:
            result["actions"].append(final_screenshot)

        return result

    except Exception as e:
        error_msg = f"Lỗi: {str(e)}\n{traceback.format_exc()}"
        with open("python/agent_error_log.txt", "a", encoding="utf-8") as f:
            f.write(error_msg)
        return {
            "success": False,
            "message": f"Lỗi khi xử lý: {str(e)}",
            "error": error_msg
        }

if __name__ == "__main__":
    try:
        # Đọc dữ liệu đầu vào từ tham số dòng lệnh
        if len(sys.argv) > 1:
            input_data = json.loads(sys.argv[1])
            instruction = input_data.get("instruction", "")
            agent_config = input_data.get("config", {})

            # Xử lý chỉ thị
            result = process_instruction(instruction, agent_config)

            # In kết quả dưới dạng JSON
            print(json.dumps(result, ensure_ascii=False))
        else:
            print(json.dumps({"success": False, "message": "Không có dữ liệu đầu vào"}))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "message": f"Lỗi: {str(e)}",
            "error": traceback.format_exc()
        }, ensure_ascii=False))
