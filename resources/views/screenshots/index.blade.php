@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Công Cụ Chụp Ảnh Màn Hình</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <form id="screenshot-form" class="mb-4">
                            <div class="form-group mb-3">
                                <label for="text">Nội dung hiển thị:</label>
                                <input type="text" class="form-control" id="text" name="text" value="Ảnh chụp màn hình">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-camera me-2"></i> Chụp Ảnh Màn Hình
                            </button>
                        </form>
                    </div>

                    <div id="results" class="d-none">
                        <h5 class="mb-3">Kết Quả</h5>
                        <div class="alert alert-success mb-3" id="success-message"></div>

                        <div class="text-center mb-3">
                            <img id="screenshot-image" src="" alt="Screenshot" class="img-fluid border rounded">
                        </div>

                        <div class="d-flex justify-content-center mb-3">
                            <a id="download-link" href="" target="_blank" class="btn btn-success me-2">
                                <i class="fas fa-download me-2"></i> Tải Ảnh
                            </a>
                            <a id="view-link" href="" target="_blank" class="btn btn-info">
                                <i class="fas fa-external-link-alt me-2"></i> Xem Ảnh Gốc
                            </a>
                        </div>

                        <div class="mb-3">
                            <h6>Thông Tin Ảnh:</h6>
                            <div id="screenshot-info" class="bg-light p-3 rounded"></div>
                        </div>
                    </div>

                    <div id="error-container" class="d-none">
                        <div class="alert alert-danger" id="error-message"></div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Hướng Dẫn</h5>
                </div>
                <div class="card-body">
                    <p>Công cụ này tạo ảnh chụp màn hình mô phỏng dựa trên PHP với thư viện GD.</p>
                    <p>Cách sử dụng:</p>
                    <ol>
                        <li>Nhập nội dung bạn muốn hiển thị trên ảnh</li>
                        <li>Nhấn nút "Chụp Ảnh Màn Hình"</li>
                        <li>Tải về hoặc xem ảnh đã tạo</li>
                    </ol>
                    <p>Các ảnh được lưu trong thư mục <code>storage/app/public/screenshots</code>.</p>

                    <div class="mt-3">
                        <a href="{{ url('/check_gd.php') }}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info-circle me-1"></i> Kiểm Tra Thư Viện GD
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-home me-1"></i> Trang Chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('screenshot-form');
    const results = document.getElementById('results');
    const errorContainer = document.getElementById('error-container');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const screenshotImage = document.getElementById('screenshot-image');
    const downloadLink = document.getElementById('download-link');
    const viewLink = document.getElementById('view-link');
    const screenshotInfo = document.getElementById('screenshot-info');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const text = document.getElementById('text').value;

        // Ẩn các phần thông báo
        results.classList.add('d-none');
        errorContainer.classList.add('d-none');

        // Hiển thị loading
        form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang xử lý...';
        form.querySelector('button[type="submit"]').disabled = true;

        // Gửi yêu cầu tạo ảnh
        fetch('/screenshots/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                text: text
            })
        })
        .then(response => response.json())
        .then(data => {
            // Đặt lại nút submit
            form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-camera me-2"></i> Chụp Ảnh Màn Hình';
            form.querySelector('button[type="submit"]').disabled = false;

            if (data.success) {
                // Hiển thị kết quả
                successMessage.textContent = data.message;
                screenshotImage.src = data.screenshot.url;
                downloadLink.href = data.screenshot.url;
                viewLink.href = data.screenshot.url;

                // Hiển thị thông tin ảnh
                screenshotInfo.innerHTML = `
                    <p><strong>Kích thước:</strong> ${data.screenshot.width}x${data.screenshot.height}</p>
                    <p><strong>Tên file:</strong> ${data.screenshot.filename}</p>
                    <p><strong>Đường dẫn:</strong> ${data.screenshot.path}</p>
                `;

                // Hiển thị kết quả
                results.classList.remove('d-none');
            } else {
                // Hiển thị lỗi
                errorMessage.textContent = data.message;
                errorContainer.classList.remove('d-none');
            }
        })
        .catch(error => {
            // Đặt lại nút submit
            form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-camera me-2"></i> Chụp Ảnh Màn Hình';
            form.querySelector('button[type="submit"]').disabled = false;

            // Hiển thị lỗi
            errorMessage.textContent = 'Lỗi khi gửi yêu cầu: ' + error.message;
            errorContainer.classList.remove('d-none');
        });
    });
});
</script>
@endpush
@endsection
