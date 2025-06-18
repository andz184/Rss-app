@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Trợ lý AI Giọng nói</h1>
    <p>Đây là giao diện của ứng dụng AI được nhúng từ Python/Flask. Vui lòng đảm bảo server AI đang chạy.</p>

    <div class="card mb-4">
        <div class="card-body">
            <style>
                .iframe-container {
                    position: relative;
                    width: 100%;
                    overflow: hidden;
                    padding-top: 66.66%; /* 3:2 Aspect Ratio */
                }
                .iframe-container iframe {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    border: 0;
                }
            </style>
            <div class="iframe-container">
                <iframe src="http://localhost:5000" title="Streaming AI Assistant" allow="microphone"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
