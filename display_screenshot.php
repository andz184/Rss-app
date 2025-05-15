<?php
// Đường dẫn đến thư mục ảnh chụp màn hình
$screenshotsDir = __DIR__ . '/storage/app/public/screenshots';
$publicUrl = '/storage/screenshots';

// Lấy danh sách tất cả các file ảnh
$files = scandir($screenshotsDir);
$screenshots = [];

foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }

    // Lấy thông tin file
    $filePath = $screenshotsDir . '/' . $file;
    if (is_file($filePath) && preg_match('/\.(png|jpg|jpeg|gif)$/i', $file)) {
        $fileUrl = $publicUrl . '/' . $file;
        $fileInfo = [
            'name' => $file,
            'url' => $fileUrl,
            'size' => filesize($filePath),
            'modified' => date('Y-m-d H:i:s', filemtime($filePath))
        ];

        $screenshots[] = $fileInfo;
    }
}

// Sắp xếp theo thời gian chỉnh sửa (mới nhất lên đầu)
usort($screenshots, function($a, $b) {
    return strtotime($b['modified']) - strtotime($a['modified']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ảnh chụp màn hình từ Agent-S</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .screenshot-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .screenshot-card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Ảnh chụp màn hình từ Agent-S</h1>

        <div class="mb-4">
            <a href="/" class="btn btn-primary">Quay lại trang chủ</a>
            <a href="/agent-tasks/create" class="btn btn-success">Tạo tác vụ mới</a>
        </div>

        <?php if (empty($screenshots)): ?>
            <div class="alert alert-info">
                Chưa có ảnh chụp màn hình nào.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($screenshots as $screenshot): ?>
                    <div class="col">
                        <div class="card screenshot-card">
                            <img src="<?php echo $screenshot['url']; ?>" class="card-img-top" alt="Screenshot">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($screenshot['name']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Dung lượng: <?php echo round($screenshot['size'] / 1024, 2); ?> KB<br>
                                        Chụp lúc: <?php echo $screenshot['modified']; ?>
                                    </small>
                                </p>
                                <a href="<?php echo $screenshot['url']; ?>" class="btn btn-primary" target="_blank">Xem ảnh gốc</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
