<?php
/**
 * Script copy ảnh từ thư mục screenshots vào storage/app/public/screenshots
 */

// Thư mục nguồn và đích
$sourceDir = __DIR__ . '/../screenshots';
$targetDir = __DIR__ . '/../storage/app/public/screenshots';

// Đảm bảo thư mục đích tồn tại
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0777, true)) {
        die("Không thể tạo thư mục $targetDir");
    }
    echo "Đã tạo thư mục $targetDir\n";
}

// Kiểm tra thư mục nguồn tồn tại
if (!is_dir($sourceDir)) {
    die("Thư mục nguồn $sourceDir không tồn tại");
}

// Quét tất cả các file trong thư mục nguồn
$files = scandir($sourceDir);
$count = 0;

foreach ($files as $file) {
    // Bỏ qua . và ..
    if ($file === '.' || $file === '..') {
        continue;
    }

    // Đường dẫn đầy đủ
    $sourcePath = $sourceDir . '/' . $file;
    $targetPath = $targetDir . '/' . $file;

    // Chỉ xử lý file (không xử lý thư mục)
    if (is_file($sourcePath)) {
        // Copy file
        if (copy($sourcePath, $targetPath)) {
            echo "Đã copy file $file\n";
            $count++;
        } else {
            echo "Lỗi khi copy file $file\n";
        }
    }
}

echo "\nHoàn thành! Đã copy $count file.\n";
?>
