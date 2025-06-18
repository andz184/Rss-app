import os
from flask import Flask, render_template, request
from flask_socketio import SocketIO
from dotenv import load_dotenv
import threading
import time
import base64
from src.api.grok import get_ai_response, clear_conversation_history
from src.api.elevenlabs import get_text_to_speech

# Load environment variables
load_dotenv()

app = Flask(__name__)
socketio = SocketIO(app, cors_allowed_origins="*", async_mode='threading')

# Biến toàn cục để quản lý việc ngắt phát âm thanh
stop_audio_flags = {}

# Biến toàn cục để quản lý trạng thái triệt echo
echo_states = {}

# Biến toàn cục để lưu lịch sử input gần nhất
last_inputs = {}

# Kiểm tra API key khi khởi động
groq_key = os.getenv("GROQ_API_KEY")
if not groq_key or groq_key == "YOUR_GROQ_API_KEY":
    print("\n⚠️ CẢNH BÁO: GROQ_API_KEY chưa được cấu hình!")
    print("Vui lòng cập nhật file .env với API key của Groq.")
    print("Đăng ký tại: https://console.groq.com/keys\n")

@app.route('/')
def index():
    return render_template('index.html')

@socketio.on('connect')
def handle_connect():
    sid = request.sid
    print(f'Client connected: {sid}')
    # Tạo flag ngắt cho mỗi client kết nối
    stop_audio_flags[sid] = False
    # Khởi tạo trạng thái triệt echo
    echo_states[sid] = {
        'assistant_speaking': False,
        'last_audio_end_time': 0,
        'echo_protection_active': False,
        'last_transcript': '',
        'last_transcript_time': 0
    }
    # Khởi tạo lịch sử input
    last_inputs[sid] = {'text': '', 'time': 0}

@socketio.on('disconnect')
def handle_disconnect():
    sid = request.sid
    print(f'Client disconnected: {sid}')
    # Xóa flag khi client ngắt kết nối
    if sid in stop_audio_flags:
        del stop_audio_flags[sid]
    # Xóa trạng thái triệt echo
    if sid in echo_states:
        del echo_states[sid]
    # Xóa lịch sử input
    if sid in last_inputs:
        del last_inputs[sid]

@socketio.on('user_speech')
def handle_user_speech(data):
    """Xử lý giọng nói của người dùng và tạo phản hồi AI"""
    # Chuẩn hóa input: loại bỏ khoảng trắng thừa
    user_text = data.get('text', '').strip()

    # Nếu input rỗng sau khi chuẩn hóa, bỏ qua
    if not user_text:
        return

    use_history = data.get('use_history', True)  # Mặc định sử dụng lịch sử

    # Lấy session ID của client
    sid = request.sid
    current_time = time.time()

    # Kiểm tra trùng lặp input
    if sid in last_inputs:
        time_since_last_input = current_time - last_inputs[sid]['time']
        if (user_text == last_inputs[sid]['text'] and
            time_since_last_input < 2.0):  # 2 giây
            print(f"Đã bỏ qua input trùng lặp: {user_text} (sau {time_since_last_input:.2f}s)")
            return

    # Cập nhật input mới nhất
    last_inputs[sid] = {
        'text': user_text,
        'time': current_time
    }

    print(f"Nhận được giọng nói: {user_text}")
    print(f"Sử dụng lịch sử trò chuyện: {use_history}")

    # Kiểm tra bảo vệ echo
    if sid in echo_states:
        echo_time_threshold = 1.5  # Tăng lên 1.5 giây để đảm bảo
        time_since_audio_end = current_time - echo_states[sid]['last_audio_end_time']

        if echo_states[sid]['echo_protection_active'] and time_since_audio_end < echo_time_threshold:
            print(f"Đã bỏ qua âm thanh có thể là echo (sau {time_since_audio_end:.2f}s khi trợ lý nói xong)")
            socketio.emit('echo_detected', {
                'message': 'Phát hiện tiếng vọng, đã bỏ qua để tránh trùng lặp'
            }, room=sid)
            return

        # Tắt bảo vệ echo sau khi đã qua thời gian ngưỡng
        if time_since_audio_end >= echo_time_threshold:
            echo_states[sid]['echo_protection_active'] = False

    # Đặt cờ dừng để ngắt bất kỳ phản hồi âm thanh nào đang phát
    if sid in stop_audio_flags:
        stop_audio_flags[sid] = True
        socketio.emit('stop_audio', room=sid)
        time.sleep(0.1)  # Đợi để đảm bảo việc dừng được xử lý

    # Bắt đầu tạo phản hồi AI trong một luồng riêng
    def process_response():
        try:
            # Đặt lại cờ dừng cho phản hồi mới
            if sid in stop_audio_flags:
                stop_audio_flags[sid] = False

            # Gửi thông báo đang xử lý
            socketio.emit('processing_status', {'status': 'Đang xử lý yêu cầu...'}, room=sid)

            # Nhận phản hồi từ Groq API, có tùy chọn sử dụng lịch sử
            ai_response = get_ai_response(user_text, use_history=use_history)

            # Gửi phản hồi văn bản ngay khi có
            socketio.emit('assistant_response', {'text': ai_response}, room=sid)

            # Thông báo bắt đầu chuyển văn bản thành giọng nói
            socketio.emit('processing_status', {'status': 'Đang tạo giọng nói...'}, room=sid)

            # Bắt đầu stream audio ngay khi có phản hồi văn bản
            if sid in echo_states:
                echo_states[sid]['assistant_speaking'] = True

            audio_thread = threading.Thread(target=lambda: stream_audio(ai_response, sid))
            audio_thread.daemon = True
            audio_thread.start()

        except Exception as e:
            print(f"Lỗi khi xử lý phản hồi: {str(e)}")
            socketio.emit('error', {'message': f"Lỗi: {str(e)}"}, room=sid)

    # Bắt đầu xử lý trong một luồng riêng
    response_thread = threading.Thread(target=process_response)
    response_thread.daemon = True
    response_thread.start()

@socketio.on('interrupt_speech')
def handle_interrupt():
    """Xử lý khi người dùng ngắt lời trợ lý"""
    sid = request.sid
    if sid in stop_audio_flags:
        stop_audio_flags[sid] = True
        print(f"Người dùng {sid} đã ngắt lời trợ lý")
        socketio.emit('stop_audio', room=sid)

@socketio.on('clear_history')
def handle_clear_history():
    """Xóa lịch sử trò chuyện"""
    try:
        message = clear_conversation_history()
        sid = request.sid
        socketio.emit('history_cleared', {'status': 'success', 'message': message}, room=sid)
        print("Đã xóa lịch sử trò chuyện")
    except Exception as e:
        print(f"Lỗi khi xóa lịch sử: {str(e)}")
        socketio.emit('error', {'message': f"Lỗi khi xóa lịch sử: {str(e)}"}, room=request.sid)

def stream_audio(text, sid, speed=1.45):
    """Stream audio từ văn bản"""
    try:
        chunk_count = 0
        start_time = time.time()

        # Kiểm tra cờ dừng trước khi bắt đầu
        if sid in stop_audio_flags and stop_audio_flags[sid]:
            print(f"Đã hủy phát âm thanh cho {sid} trước khi bắt đầu")
            return

        # Stream audio từng chunk
        for audio_chunk in get_text_to_speech(text, speed=speed):
            # Kiểm tra cờ dừng sau mỗi chunk
            if sid in stop_audio_flags and stop_audio_flags[sid]:
                print(f"Ngắt streaming audio sau {chunk_count} chunks")
                break

            if audio_chunk:
                # Chuyển đổi chunk sang base64 để gửi qua WebSocket
                base64_chunk = base64.b64encode(audio_chunk).decode('utf-8')

                # Gửi chunk âm thanh trực tiếp đến client dưới dạng base64
                socketio.emit('assistant_audio_chunk', {'chunk': base64_chunk, 'format': 'base64'}, room=sid)
                chunk_count += 1

        # Báo hiệu kết thúc streaming
        socketio.emit('audio_end', room=sid)

        # Cập nhật trạng thái triệt echo
        if sid in echo_states:
            echo_states[sid]['assistant_speaking'] = False
            echo_states[sid]['last_audio_end_time'] = time.time()
            echo_states[sid]['echo_protection_active'] = True
            print(f"Đã bật bảo vệ chống echo cho {sid}")

        # Ghi log thông tin về thời gian xử lý
        process_time = time.time() - start_time
        print(f"Đã gửi {chunk_count} chunks âm thanh trong {process_time:.2f} giây")

    except Exception as e:
        print(f"Lỗi khi stream audio: {str(e)}")
        socketio.emit('error', {'message': f"Lỗi audio: {str(e)}"}, room=sid)

if __name__ == '__main__':
    print("\n---- Trợ Lý AI Giọng Nói ----")
    print("Mở http://localhost:5000 trong trình duyệt của bạn.")
    print("API cần thiết: GROQ_API_KEY trong file .env")
    print("Tính năng: Nhận dạng tiếng nói -> Phản hồi AI -> Chuyển văn bản thành giọng nói")
    print("Có hỗ trợ lưu lịch sử trò chuyện")
    print("Có thể ngắt lời trợ lý khi nói")
    print("Hỗ trợ triệt tiếng vọng (echo cancellation)")
    print("Tốc độ nói nhanh hơn (x1.45)")
    print("----------------------------\n")
    socketio.run(app, host='0.0.0.0', port=5000, debug=False, allow_unsafe_werkzeug=True)
