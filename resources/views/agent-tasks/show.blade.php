@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('agent-tasks.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi Tiết Tác Vụ</h5>
            <span class="badge {{
                $task->status == 'completed' ? 'bg-success' :
                ($task->status == 'failed' ? 'bg-danger' :
                ($task->status == 'running' ? 'bg-primary' :
                ($task->status == 'cancelled' ? 'bg-warning' : 'bg-secondary')))
            }}">
                {{ ucfirst($task->status) }}
            </span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Tác Vụ #{{ $task->id }}</h6>
                    <p class="text-muted mb-0">
                        Agent: <strong>{{ $task->agent->name }}</strong>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Đã tạo: {{ $task->created_at->format('d/m/Y H:i:s') }}</p>
                    @if($task->started_at)
                        <p class="mb-0">Bắt đầu: {{ $task->started_at->format('d/m/Y H:i:s') }}</p>
                    @endif
                    @if($task->completed_at)
                        <p class="mb-0">Hoàn thành: {{ $task->completed_at->format('d/m/Y H:i:s') }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Hướng Dẫn</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $task->instruction }}</p>
                </div>
            </div>

            @if($task->output)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Kết Quả</h6>
                    </div>
                    <div class="card-body">
                        @if(is_array($task->output) && isset($task->output['content']))
                            <p class="mb-0">{!! nl2br(e($task->output['content'])) !!}</p>
                        @elseif(is_array($task->output) && isset($task->output['type']) && $task->output['type'] == 'text')
                            <p class="mb-0">{!! nl2br(e($task->output['content'])) !!}</p>
                        @elseif(is_string($task->output))
                            <p class="mb-0">{!! nl2br(e($task->output)) !!}</p>
                        @else
                            <pre class="mb-0"><code>{{ json_encode($task->output, JSON_PRETTY_PRINT) }}</code></pre>
                        @endif
                    </div>
                </div>
            @endif

            @if($task->error)
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Lỗi</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-danger">{{ $task->error }}</p>
                    </div>
                </div>
            @endif

            @if($task->actions && $task->actions->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Hành Động</h6>
                    </div>
                    <div class="card-body">
                        @foreach($task->actions as $action)
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-{{ $action->action_type == 'text' ? 'comment' : ($action->action_type == 'screenshot' ? 'camera' : 'globe') }}"></i>
                                        {{ ucfirst($action->action_type) }}
                                    </span>
                                    <span class="badge {{ $action->status == 'executed' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($action->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    @if($action->action_type == 'text')
                                        <p class="mb-0">{{ $action->action_data['content'] ?? 'Không có nội dung' }}</p>
                                    @elseif($action->action_type == 'browser')
                                        <p class="mb-0">
                                            <strong>Hành động:</strong> {{ $action->action_data['action'] ?? 'Unknown' }}<br>
                                            @if(isset($action->action_data['url']))
                                                <strong>URL:</strong> <a href="{{ $action->action_data['url'] }}" target="_blank">{{ $action->action_data['url'] }}</a>
                                            @endif
                                        </p>
                                    @elseif($action->action_type == 'screenshot')
                                        @if(isset($action->screenshot_url))
                                            <div class="text-center mb-2">
                                                <img src="{{ $action->screenshot_url }}" alt="Screenshot" class="img-fluid border rounded">
                                            </div>
                                            <p class="mb-0 text-center">
                                                <a href="{{ $action->screenshot_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> Xem ảnh gốc
                                                </a>
                                            </p>
                                        @else
                                            <p class="mb-0 text-danger">
                                                {{ $action->error ?? 'Không thể hiển thị ảnh chụp màn hình.' }}
                                            </p>
                                        @endif
                                    @else
                                        <pre class="mb-0"><code>{{ json_encode($action->action_data, JSON_PRETTY_PRINT) }}</code></pre>
                                    @endif
                                </div>
                                @if($action->executed_at)
                                    <div class="card-footer text-muted">
                                        <small>{{ $action->executed_at->format('d/m/Y H:i:s') }}</small>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Debug Info -->
            @if(isset($debugInfo) && count($debugInfo) > 0)
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Thông Tin Gỡ Lỗi</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0"><code>{{ json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>

                    <div class="mt-4">
                        <h6>Kiểm tra Thư Mục Lưu Trữ:</h6>
                        <p>Path: <code>storage/app/public/screenshots</code> và <code>python/screenshots</code></p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Ảnh chụp màn hình -->
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ảnh Chụp Màn Hình</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Biến debug toàn cục
                    $debugInfo = [];

                    // Tạo ảnh chụp màn hình trực tiếp
                    if (file_exists(base_path('php_screenshot.php'))) {
                        require_once base_path('php_screenshot.php');
                        if (function_exists('createScreenshot')) {
                            try {
                                $text = "Màn hình cho tác vụ: " . $task->instruction;
                                $screenshot = createScreenshot($text);

                                // Ưu tiên hiển thị base64 trực tiếp
                                if (isset($screenshot['base64']) && !empty($screenshot['base64'])) {
                                    ?>
                                    <h6 class="mb-3">Ảnh chụp màn hình:</h6>
                                    <div class="text-center mb-3">
                                        <img src="data:image/png;base64,{{ $screenshot['base64'] }}"
                                             alt="Screenshot" class="img-fluid border rounded" style="max-width: 100%;">
                                    </div>
                                    <?php
                                }
                                // Thử hiển thị từ URL nếu có
                                else if (isset($screenshot['url']) && !empty($screenshot['url'])) {
                                    $imageUrl = $screenshot['url'];
                                    ?>
                                    <h6 class="mb-3">Ảnh chụp màn hình từ URL:</h6>
                                    <div class="text-center mb-3">
                                        <img src="{{ $imageUrl }}"
                                             alt="Screenshot" class="img-fluid border rounded" style="max-width: 100%;">
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ $imageUrl }}"
                                           target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-external-link-alt me-1"></i> Xem ảnh gốc
                                        </a>
                                    </div>
                                    <?php
                                } else {
                                    echo '<div class="alert alert-warning">Không thể hiển thị ảnh chụp màn hình.</div>';
                                }

                                // Hiển thị thông tin debug
                                ?>
                                <div class="mt-4 p-3 bg-light rounded">
                                    <h6 class="mb-3">Thông tin Debug:</h6>
                                    <div class="small">
                                        <pre><?php print_r($debugInfo); ?></pre>
                                        <hr>
                                        <pre><?php print_r($screenshot['debug'] ?? []); ?></pre>
                                    </div>
                                </div>
                                <?php
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';

                                // Debug thông tin
                                echo '<div class="mt-3 p-3 bg-light">';
                                echo '<h6>Thông tin gỡ lỗi:</h6>';
                                echo '<p>Đường dẫn thư mục lưu trữ: <code>' . storage_path('app/public/screenshots') . '</code></p>';
                                echo '<p>Thư mục tồn tại: <code>' . (is_dir(storage_path('app/public/screenshots')) ? 'Có' : 'Không') . '</code></p>';
                                echo '<p>Thư mục có thể ghi: <code>' . (is_writable(storage_path('app/public/screenshots')) ? 'Có' : 'Không') . '</code></p>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning">Hàm createScreenshot không tồn tại</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">File php_screenshot.php không tồn tại</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                @if($task->status == 'running' || $task->status == 'pending')
                    <form action="{{ route('agent-tasks.cancel', $task) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-stop me-2"></i>Dừng tác vụ
                        </button>
                    </form>
                @else
                    <a href="{{ route('agent-tasks.create', ['agent_id' => $task->agent_id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo tác vụ mới
                    </a>
                @endif

                <form action="{{ route('agent-tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tác vụ này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash-alt me-2"></i>Xóa tác vụ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
