<?php
/**
 * Tệp này sẽ tạo ảnh chụp màn hình giả lập cho các tác vụ agent
 */

// Thử tất cả các thư mục có thể sử dụng được
$possibleDirs = [
    __DIR__ . '/storage/app/public/screenshots',
    dirname(__DIR__) . '/storage/app/public/screenshots',
    __DIR__ . '/public/screenshots',
    'screenshots'
];

// Đặt debug mode
$isDebug = true; // Luôn bật debug để xem thông tin

// Tìm thư mục phù hợp
$screenshotsDir = null;

foreach ($possibleDirs as $dir) {
    // Kiểm tra hoặc tạo thư mục
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
        @chmod($dir, 0777);
    }

    // Kiểm tra quyền ghi
    if (is_dir($dir) && is_writable($dir)) {
        $screenshotsDir = $dir;
        break;
    }
}

// Nếu không tìm được thư mục nào, dùng thư mục tạm
if ($screenshotsDir === null) {
    $screenshotsDir = sys_get_temp_dir();
}

// Tạo thư mục public/screenshots trực tiếp
$publicScreenshots = __DIR__ . '/public/screenshots';
if (!is_dir($publicScreenshots)) {
    @mkdir($publicScreenshots, 0777, true);
    @chmod($publicScreenshots, 0777);
}

// In thư mục đang sử dụng (để debug)
if ($isDebug) {
    if (!isset($debugInfo)) $debugInfo = [];
    $debugInfo['screenshot_dir'] = $screenshotsDir;
    $debugInfo['dir_exists'] = is_dir($screenshotsDir) ? 'Yes' : 'No';
    $debugInfo['dir_writable'] = is_writable($screenshotsDir) ? 'Yes' : 'No';
    $debugInfo['public_dir'] = $publicScreenshots;
    $debugInfo['public_dir_exists'] = is_dir($publicScreenshots) ? 'Yes' : 'No';
    $debugInfo['public_dir_writable'] = is_writable($publicScreenshots) ? 'Yes' : 'No';
}

/**
 * Tạo ảnh mô phỏng màn hình
 * @param string $text Nội dung cần hiển thị trên ảnh
 * @return array Thông tin về ảnh đã tạo
 */
function createScreenshot($text, $width = 1280, $height = 720) {
    global $screenshotsDir, $publicScreenshots, $isDebug, $debugInfo;

    // Thông tin debug
    $debug = [];

    // Tạo ảnh mới
    $image = imagecreatetruecolor($width, $height);
    if (!$image) {
        throw new Exception("Không thể tạo ảnh. GD có thể chưa được cài đặt.");
    }

    // Các màu
    $bg = imagecolorallocate($image, 245, 245, 245);
    $textColor = imagecolorallocate($image, 33, 33, 33);
    $headerBg = imagecolorallocate($image, 59, 89, 152);
    $headerText = imagecolorallocate($image, 255, 255, 255);
    $borderColor = imagecolorallocate($image, 220, 220, 220);

    // Tạo nền
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);

    // Vẽ thanh tiêu đề
    imagefilledrectangle($image, 0, 0, $width, 50, $headerBg);

    // Thêm logo hoặc tiêu đề
    $title = "Agent-S Screenshot";
    $font = 5; // Font mặc định của GD
    imagestring($image, $font, 20, 15, $title, $headerText);

    // Thêm ngày giờ vào góc phải
    $date = date('Y-m-d H:i:s');
    $dateWidth = imagefontwidth($font) * strlen($date);
    imagestring($image, $font, $width - $dateWidth - 20, 15, $date, $headerText);

    // Vẽ một số phần tử UI mô phỏng
    // Sidebar
    imagefilledrectangle($image, 0, 50, 200, $height, imagecolorallocate($image, 250, 250, 250));
    imagestring($image, 3, 20, 70, "Dashboard", $textColor);
    imagestring($image, 3, 20, 100, "Agents", $textColor);
    imagestring($image, 3, 20, 130, "Tasks", $textColor);
    imagestring($image, 3, 20, 160, "Settings", $textColor);

    // Nội dung chính
    $contentX = 220;
    $contentY = 80;
    $contentWidth = $width - $contentX - 20;
    $contentHeight = 300;

    // Vẽ hộp nội dung
    imagerectangle($image, $contentX, $contentY, $contentX + $contentWidth, $contentY + $contentHeight, $borderColor);
    imagefilledrectangle($image, $contentX + 1, $contentY + 1, $contentX + $contentWidth - 1, $contentY + 40, imagecolorallocate($image, 240, 240, 240));

    // Tiêu đề nội dung
    imagestring($image, 5, $contentX + 15, $contentY + 12, "Task Result", $textColor);

    // Thêm nội dung từ text
    $lines = explode("\n", wordwrap($text, 80, "\n"));
    $lineHeight = 25;
    $startY = $contentY + 60;

    foreach ($lines as $index => $line) {
        imagestring($image, 4, $contentX + 15, $startY + ($index * $lineHeight), $line, $textColor);
    }

    // Lưu ảnh
    $timestamp = time();
    $filename = 'screenshot_' . $timestamp . '.png';

    // Các đường dẫn khác nhau để thử
    $filePaths = [
        'storage' => $screenshotsDir . '/' . $filename,
        'public' => $publicScreenshots . '/' . $filename,
        'temp' => sys_get_temp_dir() . '/' . $filename
    ];

    // Lưu kết quả
    $saveResults = [];
    $successPath = null;

    // Thử lưu vào từng đường dẫn
    foreach ($filePaths as $type => $path) {
        $result = @imagepng($image, $path);
        $saveResults[$type] = [
            'path' => $path,
            'success' => $result ? 'Yes' : 'No',
            'exists_after' => file_exists($path) ? 'Yes' : 'No',
            'dir_writable' => is_writable(dirname($path)) ? 'Yes' : 'No'
        ];

        if ($result && file_exists($path)) {
            $successPath = $path;
        }
    }

    // Thêm thông tin debug
    $debug['save_attempts'] = $saveResults;
    $debug['final_path'] = $successPath;

    // Nếu không lưu được, bỏ qua và tạo ảnh base64
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    $base64 = base64_encode($imageData);

    // Thêm thông tin debug
    $debug['base64_created'] = 'Yes';

    imagedestroy($image);

    // Xác định URL tương đối
    $url = null;
    $actualPath = null;

    if ($successPath) {
        if (strpos($successPath, 'public/screenshots') !== false) {
            $url = '/screenshots/' . $filename;
            $actualPath = $successPath;
        } else if (strpos($successPath, 'storage/app/public') !== false) {
            $url = '/storage/screenshots/' . $filename;
            $actualPath = $successPath;
        } else {
            $url = null;
            $actualPath = $successPath;
        }
    }

    // Lưu thông tin debug
    if ($isDebug) {
        $debugInfo['screenshot_debug'] = $debug;
    }

    return [
        'path' => $actualPath,
        'url' => $url,
        'filename' => $filename,
        'width' => $width,
        'height' => $height,
        'base64' => $base64,
        'debug' => $debug
    ];
}

// Chỉ xử lý nếu được gọi trực tiếp (không phải include)
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // Kiểm tra xem GD có được cài đặt không
    if (!function_exists('imagecreatetruecolor')) {
        die(json_encode([
            'success' => false,
            'message' => 'PHP GD Library không được cài đặt. Vui lòng cài đặt thư viện GD để sử dụng tính năng này.'
        ]));
    }

    // Lấy tham số từ GET hoặc POST
    $text = $_REQUEST['text'] ?? 'Đây là ảnh chụp màn hình được tạo bằng PHP';

    try {
        // Tạo ảnh và trả về thông tin
        $screenshot = createScreenshot($text);

        if (isset($_REQUEST['json'])) {
            // Trả về JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'screenshot' => $screenshot
            ]);
        } else {
            // Hiển thị ảnh
            header('Content-Type: image/png');
            echo base64_decode($screenshot['base64']);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
