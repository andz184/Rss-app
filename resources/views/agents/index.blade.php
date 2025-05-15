@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Agent AI</h1>
        <div>
            <a href="{{ route('agents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tạo Agent Mới
            </a>
            <a href="{{ url('/kiem-tra-python') }}" class="btn btn-info">{{ __('Kiểm tra Python') }}</a>
        </div>
    </div>

    <div class="row">
        @if($agents->count() > 0)
            @foreach($agents as $agent)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 {{ $agent->is_active ? 'border-success' : 'border-secondary' }}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-robot me-2"></i>{{ $agent->name }}
                            </h5>
                            <div class="d-flex">
                                <span class="badge {{ $agent->is_active ? 'bg-success' : 'bg-secondary' }} me-2">
                                    {{ $agent->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $agent->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $agent->id }}">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('agents.show', $agent) }}">
                                                <i class="fas fa-eye me-2"></i> Xem Chi tiết
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('agents.edit', $agent) }}">
                                                <i class="fas fa-edit me-2"></i> Chỉnh sửa
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('agent-tasks.create', ['agent_id' => $agent->id]) }}">
                                                <i class="fas fa-play me-2"></i> Chạy Tác vụ
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('agents.toggle-active', $agent) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas {{ $agent->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} me-2"></i>
                                                    {{ $agent->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i> Xóa
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="card-text">{{ $agent->description ?? 'Không có mô tả.' }}</p>
                            </div>
                            <div class="agent-specs">
                                <div class="spec-item d-flex align-items-center mb-2">
                                    <div class="spec-icon me-2">
                                        <i class="fas fa-server"></i>
                                    </div>
                                    <div class="spec-content">
                                        <small class="text-muted d-block">Nhà cung cấp</small>
                                        <span>{{ ucfirst($agent->model_provider) }}</span>
                                    </div>
                                </div>
                                <div class="spec-item d-flex align-items-center mb-2">
                                    <div class="spec-icon me-2">
                                        <i class="fas fa-microchip"></i>
                                    </div>
                                    <div class="spec-content">
                                        <small class="text-muted d-block">Mô hình</small>
                                        <span>{{ $agent->model_name }}</span>
                                    </div>
                                </div>
                                <div class="spec-item d-flex align-items-center">
                                    <div class="spec-icon me-2">
                                        <i class="fas fa-desktop"></i>
                                    </div>
                                    <div class="spec-content">
                                        <small class="text-muted d-block">Nền tảng</small>
                                        <span>{{ ucfirst($agent->platform) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="{{ route('agents.show', $agent) }}" class="btn btn-sm btn-outline-primary w-100">
                                Xem Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="empty-state mb-4">
                            <i class="fas fa-robot fa-4x text-muted mb-3"></i>
                            <h4>Không Tìm Thấy Agent</h4>
                            <p class="text-muted">Bạn chưa tạo bất kỳ Agent AI nào.</p>
                        </div>
                        <a href="{{ route('agents.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Tạo Agent Đầu Tiên Của Bạn
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirm deletion
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Bạn có chắc chắn muốn xóa agent này? Hành động này không thể hoàn tác.')) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endpush
@endsection
