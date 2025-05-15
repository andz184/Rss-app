<?php

/**
 * Tập lệnh thiết lập cron job cho ứng dụng Laravel RSS Reader
 * --------------------------------------------------------
 *
 * Tập lệnh này giúp thiết lập cron job để tự động chạy lịch trình Laravel
 * Nếu bạn đang sử dụng hệ thống Linux/Unix, bạn có thể sử dụng cron
 * Nếu bạn đang sử dụng Windows, bạn nên sử dụng Task Scheduler
 */

$currentDir = __DIR__;
$phpPath = PHP_BINARY;
$artisanPath = $currentDir . DIRECTORY_SEPARATOR . 'artisan';

// Tạo dòng lệnh cron cho Linux/Unix
$cronCommand = "* * * * * cd {$currentDir} && $phpPath artisan schedule:run >> /dev/null 2>&1";

// Tạo dòng lệnh cho Windows Task Scheduler
$windowsCommand = "$phpPath $artisanPath schedule:run";

// Hướng dẫn sử dụng
echo "=======================================================================\n";
echo "              THIẾT LẬP CRON JOB CHO RSS READER\n";
echo "=======================================================================\n\n";

echo "Dưới đây là hướng dẫn thiết lập cron job tự động cho ứng dụng RSS Reader:\n\n";

echo "1. CHO HỆ ĐIỀU HÀNH LINUX/UNIX:\n";
echo "   Chạy lệnh này để mở crontab:\n";
echo "   \$ crontab -e\n\n";
echo "   Sau đó thêm dòng này vào cuối file:\n";
echo "   $cronCommand\n\n";

echo "2. CHO HỆ ĐIỀU HÀNH WINDOWS:\n";
echo "   a. Mở Command Prompt với quyền Administrator\n";
echo "   b. Chạy lệnh sau (điều chỉnh đường dẫn nếu cần):\n";
echo "      schtasks /create /sc minute /mo 1 /tn \"Laravel RSS Reader Schedule\" /tr \"$windowsCommand\"\n\n";

echo "   Hoặc bạn có thể tạo thủ công trong Task Scheduler:\n";
echo "   - Mở Task Scheduler\n";
echo "   - Chọn 'Create Basic Task'\n";
echo "   - Đặt tên: 'Laravel RSS Reader Schedule'\n";
echo "   - Trigger: Daily, lặp lại mỗi 1 phút\n";
echo "   - Action: Chạy chương trình: $phpPath\n";
echo "   - Thêm arguments: $artisanPath schedule:run\n";
echo "   - Start in: $currentDir\n\n";

echo "Sau khi thiết lập cron job, hệ thống sẽ tự động:\n";
echo "- Kiểm tra lịch trình mỗi phút\n";
echo "- Cập nhật các nguồn RSS mỗi 12 giờ (vào 00:00 và 12:00)\n";
echo "=======================================================================\n";

// Hiển thị kiểm tra lịch trình
echo "Để kiểm tra lịch trình đã cấu hình, hãy chạy:\n";
echo "php artisan schedule:list\n\n";

// Để chạy thử lập tức
echo "Để chạy lệnh ngay lập tức (kiểm tra), hãy chạy:\n";
echo "php artisan feeds:fetch\n";
echo "=======================================================================\n";
