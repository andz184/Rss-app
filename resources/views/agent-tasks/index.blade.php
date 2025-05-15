@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Tác Vụ Agent</h1>
        <a href="{{ route('agent-tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tác Vụ Mới
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">#</th>
                                <th scope="col" width="15%">Agent</th>
                                <th scope="col" width="35%">Hướng dẫn</th>
                                <th scope="col" width="15%">Trạng thái</th>
                                <th scope="col" width="15%">Đã tạo</th>
                                <th scope="col" width="15%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td>{{ $task->id }}</td>
                                    <td>{{ $task->agent->name }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 350px;">
                                            {{ $task->instruction }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->status == 'completed')
                                            <span class="badge bg-success">Hoàn thành</span>
                                        @elseif($task->status == 'running')
                                            <span class="badge bg-primary">Đang chạy</span>
                                        @elseif($task->status == 'pending')
                                            <span class="badge bg-warning text-dark">Đang chờ</span>
                                        @elseif($task->status == 'failed')
                                            <span class="badge bg-danger">Thất bại</span>
                                        @elseif($task->status == 'cancelled')
                                            <span class="badge bg-secondary">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent-tasks.show', $task) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Xem Tác Vụ">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($task->status == 'pending' || $task->status == 'running')
                                                <form action="{{ route('agent-tasks.cancel', $task) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Hủy Tác Vụ">
                                                        <i class="fas fa-stop"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('agent-tasks.destroy', $task) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Xóa Tác Vụ">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $tasks->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state mb-4">
                        <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                        <h4>Không Tìm Thấy Tác Vụ</h4>
                        <p class="text-muted">Bạn chưa tạo bất kỳ tác vụ agent nào.</p>
                    </div>
                    <a href="{{ route('agent-tasks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tạo Tác Vụ Đầu Tiên Của Bạn
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Confirm deletion
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Bạn có chắc chắn muốn xóa tác vụ này? Hành động này không thể hoàn tác.')) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endpush
@endsection
