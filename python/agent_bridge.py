#!/usr/bin/env python
import sys
import json
import os
import traceback
import base64
from io import BytesIO
from PIL import Image, ImageGrab

# Đây là một phiên bản đơn giản để mô phỏng Agent-S
# Trong thực tế, bạn sẽ import các module từ Agent-S ở đây

def process_instruction(instruction, agent_config):
    """
    Xử lý chỉ thị từ Laravel và trả về kết quả
    """
    try:
        # Ghi log
        with open("python/agent_log.txt", "a", encoding="utf-8") as f:
            f.write(f"Nhận chỉ thị: {instruction}\n")
            f.write(f"Cấu hình: {json.dumps(agent_config, ensure_ascii=False)}\n")

        # Lấy ảnh chụp màn hình
        screenshot = ImageGrab.grab()
        screenshot_path = "python/screenshots/current.png"

        # Đảm bảo thư mục tồn tại
        os.makedirs("python/screenshots", exist_ok=True)
        screenshot.save(screenshot_path)

        # Mô phỏng xử lý chỉ thị
        if "youtube" in instruction.lower():
            result = {
                "success": True,
                "message": f"Đã xử lý: {instruction}",
                "actions": [
                    {
                        "type": "screenshot",
                        "data": {
                            "path": screenshot_path,
                            "width": screenshot.width,
                            "height": screenshot.height
                        }
                    },
                    {
                        "type": "browser",
                        "data": {
                            "action": "navigate",
                            "url": "https://www.youtube.com"
                        }
                    }
                ],
                "output": f"Tôi đã điều hướng đến YouTube và chụp màn hình theo yêu cầu: '{instruction}'"
            }
        else:
            # Xử lý mặc định cho các chỉ thị khác
            result = {
                "success": True,
                "message": f"Đã xử lý: {instruction}",
                "actions": [
                    {
                        "type": "screenshot",
                        "data": {
                            "path": screenshot_path,
                            "width": screenshot.width,
                            "height": screenshot.height
                        }
                    }
                ],
                "output": f"Tôi đã xử lý hướng dẫn của bạn: '{instruction}'"
            }

        # Chuyển đổi đường dẫn ảnh thành dữ liệu base64
        with open(screenshot_path, "rb") as img_file:
            img_data = base64.b64encode(img_file.read()).decode('utf-8')

        # Thêm dữ liệu ảnh vào kết quả
        for action in result["actions"]:
            if action["type"] == "screenshot":
                action["data"]["image_base64"] = img_data

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
