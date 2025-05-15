@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="page-title">Chạy Tác Vụ Agent</h1>
        <p class="text-muted">Cung cấp hướng dẫn cho Agent AI của bạn để thực thi.</p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('agent-tasks.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="agent_id" class="form-label">Chọn Agent</label>
                    <select class="form-select @error('agent_id') is-invalid @enderror" id="agent_id" name="agent_id" required>
                        @if(isset($agent))
                            <option value="{{ $agent->id }}" selected>{{ $agent->name }}</option>
                        @else
                            <option value="">-- Chọn một Agent --</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ ucfirst($agent->platform) }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('agent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="instruction" class="form-label">Hướng dẫn</label>
                    <textarea class="form-control @error('instruction') is-invalid @enderror" id="instruction" name="instruction" rows="4" placeholder="Nhập hướng dẫn cho agent (ví dụ: 'Mở Chrome và truy cập GitHub')" required>{{ old('instruction') }}</textarea>
                    @error('instruction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Giới thiệu về Tác Vụ Agent</h5>
                            <p>Agent của bạn sẽ sử dụng AI để diễn giải hướng dẫn và thực thi trên máy tính của bạn. Agent có thể:</p>
                            <ul>
                                <li>Điều hướng giao diện GUI của hệ điều hành</li>
                                <li>Mở và tương tác với các ứng dụng</li>
                                <li>Thực hiện tìm kiếm web để lấy thông tin thời gian thực</li>
                                <li>Thực hiện các tác vụ thông thường dựa trên hướng dẫn của bạn</li>
                            </ul>
                            <p class="mb-0"><strong>Quan trọng:</strong> Agent sẽ điều khiển trực tiếp máy tính của bạn. Hãy sử dụng cẩn thận.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    @if(isset($agent))
                        <a href="{{ route('agents.show', $agent) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Quay lại Agent
                        </a>
                    @else
                        <a href="{{ route('agent-tasks.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Quay lại Tác Vụ
                        </a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play me-2"></i> Chạy Tác Vụ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
