<?php
// Test chụp màn hình từ Python

// Đường dẫn đến file screenshots
$pythonScreenshotsDir = __DIR__ . '/../python/screenshots';
$storageScreenshotsDir = __DIR__ . '/../storage/app/public/screenshots';

// Tạo thư mục nếu chưa tồn tại
if (!is_dir($pythonScreenshotsDir)) {
    mkdir($pythonScreenshotsDir, 0755, true);
}

if (!is_dir($storageScreenshotsDir)) {
    mkdir($storageScreenshotsDir, 0755, true);
}

// Liệt kê các file ảnh hiện có
$pythonScreenshots = glob($pythonScreenshotsDir . '/*.png');
$storageScreenshots = glob($storageScreenshotsDir . '/*.png');

// Thử chạy Python để chụp màn hình
$pythonCommand = 'python -c "from PIL import ImageGrab; img = ImageGrab.grab(); img.save(\'' . $pythonScreenshotsDir . '/test_' . time() . '.png\')" 2>&1';
$pythonOutput = shell_exec($pythonCommand);

// Kiểm tra lại sau khi thử chụp
$pythonScreenshotsAfter = glob($pythonScreenshotsDir . '/*.png');

echo '<html>';
echo '<head><title>Test Chụp Màn Hình</title>';
echo '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    h1, h2 { color: #333; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    img { max-width: 100%; border: 1px solid #ddd; margin: 10px 0; }
</style>';
echo '</head>';
echo '<body>';
echo '<h1>Test Chụp Màn Hình</h1>';

echo '<div class="card">';
echo '<h2>Thông Tin Hệ Thống</h2>';
echo '<p>OS: ' . PHP_OS . '</p>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';

// Kiểm tra Python
$pythonVersion = shell_exec('python --version 2>&1');
if (strpos($pythonVersion, 'Python') !== false) {
    echo '<p class="success">Python: ' . htmlspecialchars($pythonVersion) . '</p>';
} else {
    echo '<p class="error">Python chưa được cài đặt hoặc không có trong PATH.</p>';
}

// Kiểm tra PIL/Pillow
$pillow = shell_exec('python -c "try: from PIL import Image; print(\'Pillow đã được cài đặt\') except ImportError: print(\'Pillow chưa được cài đặt\')" 2>&1');
echo '<p>' . htmlspecialchars($pillow) . '</p>';
echo '</div>';

echo '<div class="card">';
echo '<h2>Thử Chụp Màn Hình</h2>';
echo '<p>Lệnh thực thi: <code>' . htmlspecialchars($pythonCommand) . '</code></p>';
echo '<p>Kết quả:</p>';
echo '<pre>' . htmlspecialchars($pythonOutput) . '</pre>';

// So sánh số lượng ảnh trước và sau
if (count($pythonScreenshotsAfter) > count($pythonScreenshots)) {
    echo '<p class="success">Chụp màn hình thành công! Đã tạo ' . (count($pythonScreenshotsAfter) - count($pythonScreenshots)) . ' ảnh mới.</p>';

    // Hiển thị ảnh mới nhất
    $newScreenshots = array_diff($pythonScreenshotsAfter, $pythonScreenshots);
    foreach ($newScreenshots as $screenshot) {
        $imageUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $screenshot);
        echo '<h3>Ảnh mới tạo:</h3>';
        echo '<img src="' . $imageUrl . '" alt="Ảnh chụp màn hình" />';
    }
} else {
    echo '<p class="error">Không có ảnh mới được tạo. Có thể có lỗi xảy ra.</p>';
}
echo '</div>';

echo '<div class="card">';
echo '<h2>Thư Mục Python Screenshots</h2>';
echo '<p>Đường dẫn: <code>' . $pythonScreenshotsDir . '</code></p>';
if (count($pythonScreenshots) > 0) {
    echo '<p class="success">Tìm thấy ' . count($pythonScreenshots) . ' ảnh.</p>';
    echo '<ul>';
    foreach ($pythonScreenshots as $index => $screenshot) {
        $filename = basename($screenshot);
        echo '<li>' . $filename . ' - ' . round(filesize($screenshot) / 1024) . ' KB</li>';
        if ($index >= 4) {
            echo '<li>... và ' . (count($pythonScreenshots) - 5) . ' ảnh khác</li>';
            break;
        }
    }
    echo '</ul>';
} else {
    echo '<p class="error">Không tìm thấy ảnh nào trong thư mục.</p>';
}
echo '</div>';

echo '<div class="card">';
echo '<h2>Thư Mục Storage Screenshots</h2>';
echo '<p>Đường dẫn: <code>' . $storageScreenshotsDir . '</code></p>';
if (count($storageScreenshots) > 0) {
    echo '<p class="success">Tìm thấy ' . count($storageScreenshots) . ' ảnh.</p>';
    echo '<ul>';
    foreach ($storageScreenshots as $index => $screenshot) {
        $filename = basename($screenshot);
        echo '<li>' . $filename . ' - ' . round(filesize($screenshot) / 1024) . ' KB</li>';
        if ($index >= 4) {
            echo '<li>... và ' . (count($storageScreenshots) - 5) . ' ảnh khác</li>';
            break;
        }
    }
    echo '</ul>';
} else {
    echo '<p class="error">Không tìm thấy ảnh nào trong thư mục.</p>';
}
echo '</div>';

echo '<div class="card">';
echo '<h2>Liên Kết</h2>';
echo '<p><a href="/">Quay lại trang chủ</a></p>';
echo '<p><a href="/kiem-tra-python">Kiểm tra Python</a></p>';
echo '</div>';

echo '</body>';
echo '</html>';
