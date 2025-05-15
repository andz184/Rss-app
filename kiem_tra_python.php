<?php
// File kiểm tra cài đặt Python

// Kiểm tra phiên bản Python
$pythonVersion = shell_exec('python --version 2>&1');
$pyVersion = shell_exec('py --version 2>&1');
$pip = shell_exec('pip --version 2>&1');
$pillow = shell_exec('python -c "try: from PIL import Image; print(\'Pillow đã được cài đặt\') except ImportError: print(\'Pillow chưa được cài đặt\')" 2>&1');

echo "<html>";
echo "<head>";
echo "<title>Kiểm tra Python</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    h1 { color: #333; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    .actions { margin-top: 20px; }
    .btn { display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
    .btn:hover { background: #0056b3; }
</style>";
echo "</head>";
echo "<body>";
echo "<h1>Kiểm tra cài đặt Python</h1>";

echo "<div class='card'>";
echo "<h2>Phiên bản Python</h2>";
if (strpos($pythonVersion, 'Python') !== false) {
    echo "<p class='success'>Python đã được cài đặt: " . htmlspecialchars($pythonVersion) . "</p>";
} elseif (strpos($pyVersion, 'Python') !== false) {
    echo "<p class='success'>Python đã được cài đặt (lệnh py): " . htmlspecialchars($pyVersion) . "</p>";
} else {
    echo "<p class='error'>Python chưa được cài đặt hoặc không có trong PATH.</p>";
}
echo "</div>";

echo "<div class='card'>";
echo "<h2>Pip</h2>";
if (strpos($pip, 'pip') !== false) {
    echo "<p class='success'>Pip đã được cài đặt: " . htmlspecialchars($pip) . "</p>";
} else {
    echo "<p class='error'>Pip chưa được cài đặt.</p>";
}
echo "</div>";

echo "<div class='card'>";
echo "<h2>Thư viện Pillow</h2>";
echo "<pre>" . htmlspecialchars($pillow) . "</pre>";
if (strpos($pillow, 'đã được cài đặt') !== false) {
    echo "<p class='success'>Pillow đã được cài đặt.</p>";
} else {
    echo "<p class='error'>Pillow chưa được cài đặt.</p>";
}
echo "</div>";

echo "<div class='card'>";
echo "<h2>Thông tin hệ thống</h2>";
echo "<p>OS: " . PHP_OS . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "</div>";

echo "<div class='actions'>";
echo "<a href='cai_dat_python.bat' class='btn'>Cài đặt Python (Tự động)</a>";
echo "<a href='cai_python_manual.bat' class='btn btn-primary'>Cài đặt Python (Thủ công - Khuyên dùng)</a>";
echo "<a href='/' class='btn'>Quay lại ứng dụng</a>";
echo "</div>";

echo "</body>";
echo "</html>";
