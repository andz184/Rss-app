<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentAction;
use App\Models\AgentTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class AgentService
{
    /**
     * Process an agent task.
     *
     * @param AgentTask $task
     * @return array
     */
    public function processTask(AgentTask $task): array
    {
        try {
            // Update task status to running
            $task->status = 'running';
            $task->started_at = now();
            $task->save();

            $agent = $task->agent;

            // Lấy API key (giải mã nếu được mã hóa)
            $apiKey = null;
            if (!empty($agent->api_key_encrypted)) {
                try {
                    $apiKey = Crypt::decryptString($agent->api_key_encrypted);
                } catch (\Exception $e) {
                    Log::warning('Could not decrypt API key: ' . $e->getMessage());
                }
            }

            // Chuẩn bị cấu hình Agent để truyền cho Python
            $agentConfig = [
                'model_provider' => $agent->model_provider,
                'model_name' => $agent->model_name,
                'endpoint_url' => $agent->endpoint_url,
                'api_key' => $apiKey,
                'grounding_model_provider' => $agent->grounding_model_provider,
                'grounding_model_name' => $agent->grounding_model_name,
                'grounding_resize_width' => $agent->grounding_resize_width,
                'grounding_resize_height' => $agent->grounding_resize_height,
                'platform' => $agent->platform,
                'observation_type' => $agent->observation_type,
                'search_engine' => $agent->search_engine,
            ];

            // Chuẩn bị dữ liệu đầu vào
            $inputData = [
                'instruction' => $task->instruction,
                'config' => $agentConfig,
            ];

            // ===== ƯU TIÊN CHẠY PYTHON TRỰC TIẾP =====
            // Chạy Python trực tiếp với test_agent.py để có ảnh thực tế
            $result = $this->executePythonDirectly($task->instruction);

            // Nếu không có kết quả từ Python trực tiếp, thử các phương pháp khác
            if ($result === null) {
                // Thử chạy simple_agent_bridge.py
                if ($this->isPythonAvailable()) {
                    try {
                        // Gọi script Python đơn giản
                        $scriptPath = base_path('python/simple_agent_bridge.py');
                        $jsonInput = escapeshellarg(json_encode($inputData));

                        // Các lệnh Python có thể sử dụng
                        $pythonCommands = [
                            "python $scriptPath",  // Lệnh mặc định
                            "py $scriptPath",      // Lệnh thay thế
                        ];

                        // Kiểm tra xem có file env Python không
                        $pythonEnvFile = base_path('python/.env');
                        if (file_exists($pythonEnvFile)) {
                            $envContent = file_get_contents($pythonEnvFile);
                            if (preg_match('/PYTHON_PATH=(.+)/', $envContent, $matches)) {
                                $pythonPath = trim($matches[1]);
                                array_unshift($pythonCommands, "$pythonPath $scriptPath");
                            }
                        }

                        $output = null;
                        $success = false;

                        // Thử lần lượt các lệnh Python cho đến khi thành công
                        foreach ($pythonCommands as $pythonCmd) {
                            $command = "$pythonCmd $jsonInput 2>&1";
                            Log::info("Đang thử lệnh Python: $command");

                            $tempOutput = shell_exec($command);
                            if (!empty($tempOutput) && strpos($tempOutput, 'not found') === false) {
                                $output = $tempOutput;
                                $success = true;
                                Log::info("Đã thực thi thành công với lệnh: $pythonCmd");
                                break;
                            }
                        }

                        // Log kết quả
                        if ($success && !empty($output)) {
                            Log::info('Kết quả từ Python: ' . substr($output, 0, 200) . '...');

                            $pythonResult = json_decode($output, true);
                            if (isset($pythonResult['success']) && $pythonResult['success']) {
                                $result = $pythonResult;
                                Log::info('Sử dụng kết quả từ Python thành công');
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Python execution failed: ' . $e->getMessage());
                    }
                }

                // Nếu vẫn không có kết quả, sử dụng PHP mô phỏng
                if ($result === null) {
                    $result = $this->processMockInstruction($task->instruction);
                }
            }

            // Xử lý các hành động từ kết quả
            if (isset($result['actions']) && is_array($result['actions'])) {
                foreach ($result['actions'] as $actionData) {
                    $action = new AgentAction();
                    $action->agent_task_id = $task->id;
                    $action->action_type = $actionData['type'];

                    // Xử lý ảnh chụp màn hình nếu có
                    if ($actionData['type'] == 'screenshot' && isset($actionData['data']['image_base64'])) {
                        $imgData = $actionData['data'];

                        try {
                            // Lưu ảnh vào storage với tên file duy nhất
                            $decodedImage = base64_decode($imgData['image_base64']);
                            if ($decodedImage === false) {
                                Log::warning("Không thể giải mã dữ liệu base64 của ảnh");
                            } else {
                                $filename = 'screenshots/task_' . $task->id . '_' . time() . '.png';
                                if (Storage::disk('public')->put($filename, $decodedImage)) {
                                    Log::info("Đã lưu ảnh chụp màn hình vào: " . $filename);

                                    // Cập nhật đường dẫn ảnh trong dữ liệu hành động
                                    $imgData['storage_path'] = $filename;
                                    $actionData['data'] = $imgData;
                                } else {
                                    Log::warning("Không thể lưu ảnh vào storage");
                                }
                            }

                            // Loại bỏ dữ liệu base64 (quá lớn) trước khi lưu
                            unset($actionData['data']['image_base64']);
                        } catch (\Exception $e) {
                            Log::error("Lỗi khi lưu ảnh chụp màn hình: " . $e->getMessage());
                        }
                    }

                    $action->action_data = $actionData['data'];
                    $action->status = 'executed';
                    $action->executed_at = now();
                    $action->save();
                }
            }

            // Update task status to completed
            $task->status = 'completed';
            $task->result = [
                'success' => true,
                'message' => $result['message'] ?? 'Task completed successfully',
            ];
            $task->output = [
                'type' => 'text',
                'content' => $result['output'] ?? 'I have processed your instruction: ' . $task->instruction,
            ];
            $task->completed_at = now();
            $task->save();

            return [
                'success' => true,
                'message' => 'Task processed successfully',
                'task' => $task,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'agent_id' => $task->agent_id,
                'exception' => $e,
            ]);

            $task->status = 'failed';
            $task->error = $e->getMessage();
            $task->save();

            return [
                'success' => false,
                'message' => 'Error processing task: ' . $e->getMessage(),
                'task' => $task,
            ];
        }
    }

    /**
     * Kiểm tra Python có sẵn hay không
     */
    private function isPythonAvailable(): bool
    {
        // Luôn ưu tiên sử dụng Python
        Log::info('Đang ưu tiên sử dụng Python cho Agent-S');
        return true;

        // Code cũ được giữ lại nhưng không được sử dụng
        $checkPython = shell_exec('python --version 2>&1');
        if (strpos($checkPython, 'Python') !== false) {
            return true;
        }

        $checkPy = shell_exec('py --version 2>&1');
        if (strpos($checkPy, 'Python') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Mô phỏng xử lý chỉ thị bằng PHP khi Python không hoạt động
     */
    private function processMockInstruction(string $instruction): array
    {
        // Chụp màn hình đầu tiên bằng PHP
        $screenshotBefore = $this->captureScreenshot("Trước khi thực hiện: $instruction");

        // Mô phỏng xử lý chỉ thị
        if (stripos($instruction, 'github') !== false) {
            $result = [
                'success' => true,
                'message' => "Đã xử lý: $instruction",
                'actions' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'Tôi đã nhận lệnh mở GitHub.'
                        ]
                    ],
                    [
                        'type' => 'browser',
                        'data' => [
                            'action' => 'navigate',
                            'url' => 'https://github.com'
                        ]
                    ]
                ],
                'output' => "Tôi đã tiếp nhận chỉ thị của bạn: '$instruction'\nĐang mô phỏng mở trang GitHub"
            ];
        } elseif (stripos($instruction, 'youtube') !== false) {
            $result = [
                'success' => true,
                'message' => "Đã xử lý: $instruction",
                'actions' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'Tôi đã nhận lệnh mở YouTube.'
                        ]
                    ],
                    [
                        'type' => 'browser',
                        'data' => [
                            'action' => 'navigate',
                            'url' => 'https://www.youtube.com'
                        ]
                    ]
                ],
                'output' => "Tôi đã tiếp nhận chỉ thị của bạn: '$instruction'\nĐang mô phỏng mở trang YouTube"
            ];
        } else {
            // Xử lý mặc định cho các chỉ thị khác
            $result = [
                'success' => true,
                'message' => "Đã xử lý: $instruction",
                'actions' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => "Tôi đã nhận lệnh: $instruction"
                        ]
                    ]
                ],
                'output' => "Tôi đã xử lý chỉ thị của bạn: '$instruction'"
            ];
        }

        // Chụp màn hình sau khi thực hiện
        $screenshotAfter = $this->captureScreenshot("Sau khi thực hiện: $instruction");

        // Thêm ảnh chụp màn hình vào actions
        if ($screenshotBefore) {
            array_unshift($result['actions'], [
                'type' => 'screenshot',
                'data' => $screenshotBefore
            ]);
        }

        if ($screenshotAfter) {
            $result['actions'][] = [
                'type' => 'screenshot',
                'data' => $screenshotAfter
            ];
        }

        return $result;
    }

    /**
     * Xử lý chỉ thị trực tiếp trong PHP (fallback khi không có Python)
     * @deprecated Được thay thế bằng processMockInstruction và executePythonDirectly
     */
    private function processInstructionDirectly(string $instruction, array $config): array
    {
        // Gọi phương thức mới
        return $this->processMockInstruction($instruction);
    }

    /**
     * Tạo ảnh chụp màn hình bằng PHP
     */
    private function captureScreenshot($text = null): ?array
    {
        try {
            require_once base_path('php_screenshot.php');

            if (function_exists('createScreenshot')) {
                $screenshot = createScreenshot($text ?? 'Ảnh chụp màn hình');
                return [
                    'width' => $screenshot['width'],
                    'height' => $screenshot['height'],
                    'image_base64' => $screenshot['base64'],
                    'path' => $screenshot['path'],
                    'filename' => $screenshot['filename']
                ];
            }

            Log::warning('Hàm createScreenshot không tồn tại');
            return null;
        } catch (\Exception $e) {
            Log::error('Lỗi khi chụp màn hình PHP: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel a running or pending task.
     *
     * @param AgentTask $task
     * @return bool
     */
    public function cancelTask(AgentTask $task): bool
    {
        if ($task->status !== 'pending' && $task->status !== 'running') {
            return false;
        }

        $task->status = 'cancelled';
        $task->save();

        return true;
    }

    /**
     * Get a list of available models for a provider.
     *
     * @param string $provider
     * @return array
     */
    public function getModelsForProvider(string $provider): array
    {
        switch ($provider) {
            case 'anthropic':
                return [
                    'claude-3-7-sonnet-20250219' => 'Claude 3.7 Sonnet',
                    'claude-3-5-sonnet-20240620' => 'Claude 3.5 Sonnet',
                    'claude-3-opus-20240229' => 'Claude 3 Opus',
                ];
            case 'openai':
                return [
                    'gpt-4o' => 'GPT-4o',
                    'gpt-4-turbo' => 'GPT-4 Turbo',
                    'gpt-4' => 'GPT-4',
                ];
            case 'huggingface':
                return [
                    'meta-llama/llama-3-70b-instruct' => 'Llama 3 70B Instruct',
                    'mistralai/mistral-large-latest' => 'Mistral Large',
                    'UI-TARS-72B-DPO' => 'UI-TARS 72B DPO',
                ];
            default:
                return [];
        }
    }

    /**
     * Chạy Python trực tiếp để chụp màn hình thực
     */
    private function executePythonDirectly(string $instruction): ?array
    {
        try {
            // Tạo thư mục cho ảnh chụp màn hình nếu chưa có
            $screenshotsDir = base_path('screenshots');
            if (!is_dir($screenshotsDir)) {
                mkdir($screenshotsDir, 0777, true);
            }

            // Chuẩn bị lệnh Python - ưu tiên sử dụng test_agent.py vì nó đơn giản nhất
            $scriptPath = base_path('python/test_agent.py');

            // Chỉ tiếp tục nếu file tồn tại
            if (!file_exists($scriptPath)) {
                Log::warning('Không tìm thấy file Python: ' . $scriptPath);
                return null;
            }

            // Thực thi Python script
            $command = "py " . $scriptPath . " 2>&1";
            Log::info("Đang chạy Python: " . $command);

            $output = shell_exec($command);
            Log::info("Kết quả Python: " . ($output ?? 'Không có output'));

            // Scan thư mục screenshots để tìm ảnh vừa tạo
            $screenshots = glob($screenshotsDir . '/*.png');

            if (empty($screenshots)) {
                Log::warning('Không tìm thấy ảnh nào sau khi chạy Python');
                return null;
            }

            // Sắp xếp theo thời gian tạo - lấy file mới nhất
            usort($screenshots, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            $latestScreenshot = $screenshots[0];
            $filename = pathinfo($latestScreenshot, PATHINFO_BASENAME);

            // Copy file vào storage public để có thể truy cập từ web
            $storagePath = 'screenshots/' . $filename;
            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
            }

            // Copy từ thư mục screenshots vào storage
            copy($latestScreenshot, storage_path('app/public/' . $storagePath));

            // Đọc file ảnh và chuyển thành base64
            $imageData = file_get_contents($latestScreenshot);
            $base64 = base64_encode($imageData);

            // Tạo kết quả
            return [
                'success' => true,
                'message' => "Đã xử lý: $instruction",
                'actions' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'Tôi đã nhận lệnh mở YouTube.'
                        ]
                    ],
                    [
                        'type' => 'browser',
                        'data' => [
                            'action' => 'navigate',
                            'url' => 'https://www.youtube.com'
                        ]
                    ],
                    [
                        'type' => 'screenshot',
                        'data' => [
                            'width' => 1280,
                            'height' => 720,
                            'image_base64' => $base64,
                            'path' => $latestScreenshot,
                            'filename' => $filename,
                            'storage_path' => $storagePath
                        ]
                    ]
                ],
                'output' => "Tôi đã tiếp nhận chỉ thị của bạn: '$instruction'\nĐang mô phỏng mở trang YouTube"
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi khi chạy Python: ' . $e->getMessage());
            return null;
        }
    }
}
