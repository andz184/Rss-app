#!/usr/bin/env python
import sys
import os
import platform
import json

# Kiểm tra môi trường Python
result = {
    "python_version": sys.version,
    "platform": platform.platform(),
    "executable": sys.executable,
    "path": os.environ.get("PATH", ""),
    "working_dir": os.getcwd(),
    "script_dir": os.path.dirname(os.path.abspath(__file__)),
}

# Kiểm tra các thư viện cần thiết
try:
    import PIL
    result["pil_installed"] = True
    result["pil_version"] = PIL.__version__
except ImportError:
    result["pil_installed"] = False
    result["pil_version"] = None

try:
    from PIL import ImageGrab
    test_img = ImageGrab.grab(bbox=(0, 0, 100, 100))
    result["imagegrab_works"] = True
except Exception as e:
    result["imagegrab_works"] = False
    result["imagegrab_error"] = str(e)

# Kiểm tra webbrowser
try:
    import webbrowser
    result["webbrowser_installed"] = True
except ImportError:
    result["webbrowser_installed"] = False

# In kết quả dưới dạng JSON
print(json.dumps(result, indent=2))

# Lưu kết quả vào file
with open(os.path.join(os.path.dirname(os.path.abspath(__file__)), "env_check_result.json"), "w") as f:
    json.dump(result, f, indent=2)

print("\nThông tin môi trường đã được lưu vào file env_check_result.json")
