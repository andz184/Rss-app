<?php
// Kiểm tra thư viện GD
echo '<h1>Kiểm tra thư viện GD</h1>';

if (function_exists('gd_info')) {
    echo '<p style="color:green">Thư viện GD đã được cài đặt.</p>';

    $gdInfo = gd_info();
    echo '<h2>Thông tin GD:</h2>';
    echo '<pre>';
    print_r($gdInfo);
    echo '</pre>';

    // Thử tạo ảnh
    echo '<h2>Thử tạo ảnh:</h2>';
    try {
        // Tạo ảnh mới
        $image = imagecreatetruecolor(300, 200);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);

        // Đổ màu nền
        imagefill($image, 0, 0, $bgColor);

        // Vẽ văn bản
        imagestring($image, 5, 50, 80, 'Test GD Library', $textColor);

        // Output ảnh
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $base64 = base64_encode($imageData);
        echo '<p>Tạo ảnh thành công:</p>';
        echo '<img src="data:image/png;base64,' . $base64 . '" alt="Test GD Image">';

        // Kiểm tra các thư mục
        echo '<h2>Kiểm tra thư mục lưu trữ:</h2>';
        $dirs = [
            'storage/app/public/screenshots' => realpath(__DIR__ . '/../storage/app/public/screenshots'),
            'public/storage/screenshots' => realpath(__DIR__ . '/storage/screenshots'),
            'temp directory' => sys_get_temp_dir()
        ];

        echo '<table border="1" cellpadding="5">';
        echo '<tr><th>Thư mục</th><th>Đường dẫn thực tế</th><th>Tồn tại</th><th>Có thể ghi</th><th>Thử ghi</th></tr>';

        foreach ($dirs as $name => $path) {
            echo '<tr>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $path . '</td>';
            echo '<td>' . (is_dir($path) ? 'Có' : 'Không') . '</td>';
            echo '<td>' . (is_writable($path) ? 'Có' : 'Không') . '</td>';

            // Thử ghi file
            $testFile = $path . '/test_' . time() . '.txt';
            $writeSuccess = @file_put_contents($testFile, 'Test write permission');
            if ($writeSuccess) {
                echo '<td style="color:green">Thành công</td>';
                @unlink($testFile); // Xóa file test
            } else {
                echo '<td style="color:red">Thất bại</td>';
            }

            echo '</tr>';
        }
        echo '</table>';

        // Thử tạo thư mục nếu chưa tồn tại
        echo '<h2>Thử tạo thư mục:</h2>';
        $screenshotsDir = __DIR__ . '/../storage/app/public/screenshots';
        if (!is_dir($screenshotsDir)) {
            if (mkdir($screenshotsDir, 0755, true)) {
                echo '<p style="color:green">Đã tạo thư mục: ' . $screenshotsDir . '</p>';
            } else {
                echo '<p style="color:red">Không thể tạo thư mục: ' . $screenshotsDir . '</p>';
            }
        } else {
            echo '<p>Thư mục đã tồn tại: ' . $screenshotsDir . '</p>';
        }

    } catch (Exception $e) {
        echo '<p style="color:red">Lỗi: ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p style="color:red">Thư viện GD CHƯA ĐƯỢC cài đặt.</p>';
    echo '<p>Hướng dẫn cài đặt GD:</p>';
    echo '<ul>';
    echo '<li>Windows (XAMPP): Mở php.ini và uncomment dòng extension=gd</li>';
    echo '<li>Linux: sudo apt-get install php-gd</li>';
    echo '<li>Mac: brew install php@7.4-gd</li>';
    echo '</ul>';
}

// Kiểm tra phiên bản PHP và các extension
echo '<h2>Thông tin PHP:</h2>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';

echo '<h3>Extensions đã cài đặt:</h3>';
echo '<pre>';
print_r(get_loaded_extensions());
echo '</pre>';

// Kiểm tra file và thư mục screenshot
echo '<h2>Kiểm tra php_screenshot.php:</h2>';
$screenshotFile = __DIR__ . '/../php_screenshot.php';
if (file_exists($screenshotFile)) {
    echo '<p style="color:green">File php_screenshot.php tồn tại: ' . $screenshotFile . '</p>';
} else {
    echo '<p style="color:red">File php_screenshot.php KHÔNG tồn tại: ' . $screenshotFile . '</p>';
}

// Kiểm tra symlink storage
echo '<h2>Kiểm tra symlink storage:</h2>';
$publicStorage = __DIR__ . '/storage';
$targetStorage = __DIR__ . '/../storage/app/public';

if (is_link($publicStorage)) {
    echo '<p style="color:green">Symlink tồn tại: ' . $publicStorage . ' -> ' . readlink($publicStorage) . '</p>';
} else {
    echo '<p style="color:red">Symlink KHÔNG tồn tại: ' . $publicStorage . '</p>';
    echo '<p>Chạy lệnh sau để tạo symlink: <code>php artisan storage:link</code></p>';
}

echo '<p><a href="/">Quay lại trang chủ</a></p>';
?>
