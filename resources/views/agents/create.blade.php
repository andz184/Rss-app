@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="page-title">Tạo Agent Mới</h1>
        <p class="text-muted">Cấu hình agent AI của bạn để tự động hóa các tác vụ trên máy tính.</p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('agents.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Agent</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả (Tùy chọn)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-info-circle me-2"></i> Giới thiệu về Agent
                            </div>
                            <div class="card-body">
                                <p>Agent-S là một framework mã nguồn mở sử dụng máy tính như một con người. Bạn có thể cấu hình agent với các mô hình AI khác nhau để tự động hóa các tác vụ trên máy tính của bạn.</p>
                                <p class="mb-0"><strong>Quan trọng:</strong> Agent sẽ chạy trực tiếp mã Python để điều khiển máy tính của bạn. Vui lòng sử dụng cẩn thận.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Cấu hình Mô hình</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="model_provider" class="form-label">Nhà cung cấp Mô hình</label>
                            <select class="form-select @error('model_provider') is-invalid @enderror" id="model_provider" name="model_provider" required>
                                <option value="anthropic" {{ old('model_provider') == 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                                <option value="openai" {{ old('model_provider') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                <option value="huggingface" {{ old('model_provider') == 'huggingface' ? 'selected' : '' }}>HuggingFace</option>
                            </select>
                            @error('model_provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="model_name" class="form-label">Tên Mô hình</label>
                            <input type="text" class="form-control @error('model_name') is-invalid @enderror" id="model_name" name="model_name" value="{{ old('model_name', 'claude-3-7-sonnet-20250219') }}" required>
                            <div class="form-text">Ví dụ: claude-3-7-sonnet-20250219, gpt-4o, v.v.</div>
                            @error('model_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="endpoint_url" class="form-label">URL Endpoint Tùy chỉnh (Tùy chọn)</label>
                            <input type="text" class="form-control @error('endpoint_url') is-invalid @enderror" id="endpoint_url" name="endpoint_url" value="{{ old('endpoint_url') }}">
                            <div class="form-text">Chỉ cần thiết cho các endpoint mô hình tùy chỉnh.</div>
                            @error('endpoint_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">Khóa API</label>
                            <input type="password" class="form-control @error('api_key') is-invalid @enderror" id="api_key" name="api_key" value="{{ old('api_key') }}">
                            <div class="form-text">Khóa của bạn sẽ được mã hóa trước khi lưu.</div>
                            @error('api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Cấu hình Grounding</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="grounding_model_provider" class="form-label">Nhà cung cấp Mô hình Grounding (Tùy chọn)</label>
                            <select class="form-select @error('grounding_model_provider') is-invalid @enderror" id="grounding_model_provider" name="grounding_model_provider">
                                <option value="">Sử dụng cùng Mô hình chính</option>
                                <option value="anthropic" {{ old('grounding_model_provider') == 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                                <option value="openai" {{ old('grounding_model_provider') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                <option value="huggingface" {{ old('grounding_model_provider') == 'huggingface' ? 'selected' : '' }}>HuggingFace</option>
                            </select>
                            @error('grounding_model_provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="grounding_model_name" class="form-label">Tên Mô hình Grounding (Tùy chọn)</label>
                            <input type="text" class="form-control @error('grounding_model_name') is-invalid @enderror" id="grounding_model_name" name="grounding_model_name" value="{{ old('grounding_model_name') }}">
                            <div class="form-text">Để trống để sử dụng cùng mô hình chính.</div>
                            @error('grounding_model_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="grounding_resize_width" class="form-label">Chiều rộng Resize Grounding</label>
                            <input type="number" class="form-control @error('grounding_resize_width') is-invalid @enderror" id="grounding_resize_width" name="grounding_resize_width" value="{{ old('grounding_resize_width', 1366) }}">
                            <div class="form-text">Cho dự đoán tọa độ. Mặc định: 1366</div>
                            @error('grounding_resize_width')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Cấu hình Môi trường</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="platform" class="form-label">Nền tảng</label>
                            <select class="form-select @error('platform') is-invalid @enderror" id="platform" name="platform" required>
                                <option value="windows" {{ old('platform', 'windows') == 'windows' ? 'selected' : '' }}>Windows</option>
                                <option value="linux" {{ old('platform') == 'linux' ? 'selected' : '' }}>Linux</option>
                                <option value="darwin" {{ old('platform') == 'darwin' ? 'selected' : '' }}>MacOS</option>
                            </select>
                            @error('platform')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observation_type" class="form-label">Loại Quan sát</label>
                            <select class="form-select @error('observation_type') is-invalid @enderror" id="observation_type" name="observation_type" required>
                                <option value="screenshot" {{ old('observation_type', 'screenshot') == 'screenshot' ? 'selected' : '' }}>Ảnh chụp màn hình</option>
                            </select>
                            @error('observation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="search_engine" class="form-label">Công cụ Tìm kiếm</label>
                            <select class="form-select @error('search_engine') is-invalid @enderror" id="search_engine" name="search_engine" required>
                                <option value="Perplexica" {{ old('search_engine', 'Perplexica') == 'Perplexica' ? 'selected' : '' }}>Perplexica</option>
                            </select>
                            @error('search_engine')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Hoạt động</label>
                            </div>
                            <div class="form-text">Agent không hoạt động không thể được sử dụng để chạy tác vụ.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('agents.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại Danh sách Agent
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Tạo Agent
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
