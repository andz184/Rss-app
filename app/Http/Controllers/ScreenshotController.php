<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ScreenshotController extends Controller
{
    /**
     * Hiển thị trang chụp ảnh màn hình
     */
    public function index()
    {
        return view('screenshots.index');
    }

    /**
     * Tạo ảnh chụp màn hình và trả về
     */
    public function create(Request $request)
    {
        try {
            require_once base_path('php_screenshot.php');

            if (!function_exists('createScreenshot')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hàm createScreenshot không tồn tại'
                ]);
            }

            $text = $request->input('text', 'Ảnh chụp màn hình từ ' . $request->url());

            // Tạo ảnh chụp màn hình
            $screenshot = createScreenshot($text);

            // Lưu thông tin screenshot
            $filename = $screenshot['filename'];
            $path = 'screenshots/' . $filename;

            return response()->json([
                'success' => true,
                'message' => 'Đã tạo ảnh chụp màn hình',
                'screenshot' => [
                    'url' => asset('storage/' . $path),
                    'path' => $path,
                    'filename' => $filename,
                    'width' => $screenshot['width'],
                    'height' => $screenshot['height']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo ảnh chụp màn hình: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị ảnh chụp màn hình
     */
    public function show($filename)
    {
        $path = 'screenshots/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Không tìm thấy ảnh');
        }

        return response()->file(storage_path('app/public/' . $path));
    }
}
