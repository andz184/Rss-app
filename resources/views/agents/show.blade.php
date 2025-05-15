@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('agents.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title mb-0">{{ $agent->name }}</h1>
            <span class="badge {{ $agent->is_active ? 'bg-success' : 'bg-secondary' }} ms-3">
                {{ $agent->is_active ? 'Hoạt động' : 'Không hoạt động' }}
            </span>
        </div>
        <p class="text-muted mt-2">{{ $agent->description ?? 'Không có mô tả.' }}</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Agent Configuration Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cấu Hình Agent</h5>
                    <a href="{{ route('agents.edit', $agent) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Chỉnh sửa
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Thiết Lập Mô Hình</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Nhà cung cấp</th>
                                        <td>{{ ucfirst($agent->model_provider) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mô hình</th>
                                        <td>{{ $agent->model_name }}</td>
                                    </tr>
                                    @if($agent->endpoint_url)
                                        <tr>
                                            <th>URL Endpoint</th>
                                            <td>{{ $agent->endpoint_url }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Thiết Lập Grounding</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Nhà cung cấp</th>
                                        <td>{{ $agent->grounding_model_provider ? ucfirst($agent->grounding_model_provider) : 'Cùng với mô hình chính' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mô hình</th>
                                        <td>{{ $agent->grounding_model_name ?? 'Cùng với mô hình chính' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Chiều rộng Resize</th>
                                        <td>{{ $agent->grounding_resize_width ?? '1366' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Thiết Lập Môi Trường</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Nền tảng</th>
                                        <td>{{ ucfirst($agent->platform) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Quan sát</th>
                                        <td>{{ ucfirst($agent->observation_type) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Công cụ Tìm kiếm</th>
                                        <td>{{ $agent->search_engine }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Thông Tin Agent</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <th width="40%">Trạng thái</th>
                                        <td>
                                            <span class="badge {{ $agent->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $agent->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Đã tạo</th>
                                        <td>{{ $agent->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cập nhật cuối</th>
                                        <td>{{ $agent->updated_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between">
                    <div>
                        <form action="{{ route('agents.toggle-active', $agent) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $agent->is_active ? 'btn-warning' : 'btn-success' }}">
                                <i class="fas {{ $agent->is_active ? 'fa-pause me-1' : 'fa-play me-1' }}"></i>
                                {{ $agent->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                            </button>
                        </form>
                    </div>
                    <div>
                        <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash-alt me-1"></i> Xóa Agent
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Tasks Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tác Vụ Gần Đây</h5>
                    <a href="{{ route('agent-tasks.create', ['agent_id' => $agent->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Tác Vụ Mới
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentTasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTasks as $task)
                                <a href="{{ route('agent-tasks.show', $task) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 text-truncate" style="max-width: 70%;">{{ $task->instruction }}</h6>
                                        <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <small class="text-truncate" style="max-width: 70%;">
                                            @if(!empty($task->output))
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                @if(isset($task->output['content']))
                                                    {{ \Illuminate\Support\Str::limit($task->output['content'], 100) }}
                                                @else
                                                    Tác vụ hoàn thành thành công
                                                @endif
                                            @elseif($task->error)
                                                <i class="fas fa-exclamation-circle text-danger me-1"></i>
                                                {{ \Illuminate\Support\Str::limit($task->error, 100) }}
                                            @else
                                                <i class="fas fa-info-circle text-primary me-1"></i>
                                                Không có kết quả
                                            @endif
                                        </small>
                                        <span class="badge {{
                                            $task->status == 'completed' ? 'bg-success' :
                                            ($task->status == 'running' ? 'bg-primary' :
                                            ($task->status == 'pending' ? 'bg-warning text-dark' :
                                            ($task->status == 'failed' ? 'bg-danger' : 'bg-secondary')))
                                        }}">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="empty-state mb-3">
                                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                <h5>Không Tìm Thấy Tác Vụ</h5>
                                <p class="text-muted">Bạn chưa tạo bất kỳ tác vụ nào cho agent này.</p>
                            </div>
                            <a href="{{ route('agent-tasks.create', ['agent_id' => $agent->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Chạy Tác Vụ Đầu Tiên
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-center">
                    <a href="{{ route('agent-tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                        Xem Tất Cả Tác Vụ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Thao Tác</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent-tasks.create', ['agent_id' => $agent->id]) }}" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i> Chạy Tác Vụ
                        </a>
                        <a href="{{ route('agents.edit', $agent) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i> Chỉnh Sửa Agent
                        </a>
                        @if($agent->is_active)
                            <form action="{{ route('agents.toggle-active', $agent) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-pause me-2"></i> Vô Hiệu Hóa Agent
                                </button>
                            </form>
                        @else
                            <form action="{{ route('agents.toggle-active', $agent) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="fas fa-play me-2"></i> Kích Hoạt Agent
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash-alt me-2"></i> Xóa Agent
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Giới Thiệu Về Agent-S</h5>
                </div>
                <div class="card-body">
                    <p><strong>Agent-S</strong> là một framework mã nguồn mở sử dụng máy tính như một con người. Với sức mạnh của các mô hình AI hiện đại, các agent này có thể:</p>
                    <ul>
                        <li>Điều khiển máy tính của bạn thông qua giao diện trực quan</li>
                        <li>Thực hiện tìm kiếm web để lấy thông tin thời gian thực</li>
                        <li>Thực thi các tác vụ phức tạp một cách tự động</li>
                        <li>Tự động hóa các quy trình công việc lặp đi lặp lại</li>
                    </ul>
                    <p class="mb-0">Để biết thêm thông tin, hãy truy cập <a href="https://github.com/simular-ai/Agent-S" target="_blank">kho lưu trữ GitHub Agent-S</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirm deletion
        const deleteForm = document.querySelector('.delete-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(event) {
                if (!confirm('Bạn có chắc chắn muốn xóa agent này? Tất cả các tác vụ liên quan cũng sẽ bị xóa. Hành động này không thể hoàn tác.')) {
                    event.preventDefault();
                }
            });
        }
    });
</script>
@endpush
@endsection
