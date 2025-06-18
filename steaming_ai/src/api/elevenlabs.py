import os
import io
import tempfile
from gtts import gTTS
import threading
import time

# Cache cho audio đã tạo trước đó
audio_cache = {}
cache_lock = threading.Lock()

def get_text_to_speech(text, speed=1.45):
    """
    Chuyển đổi văn bản thành giọng nói tiếng Việt sử dụng Google Text-to-Speech.
    Sử dụng caching để tăng tốc các phản hồi thường xuyên.
    
    Args:
        text (str): Văn bản cần chuyển đổi
        speed (float): Hệ số tốc độ đọc (1.0 là bình thường, 1.45 là nhanh hơn 45%)
        
    Yields:
        bytes: Các đoạn dữ liệu âm thanh
    """
    print(f"gTTS bắt đầu xử lý: '{text[:50]}...'")
    
    # Chuẩn hóa văn bản
    normalized_text = text.strip()
    if not normalized_text:
        print("Văn bản trống, trả về chunk rỗng")
        yield b''
        return
    
    # Tạo cache key bao gồm cả tốc độ
    cache_key = f"{normalized_text}_{speed}"
    
    # Kiểm tra cache
    with cache_lock:
        if cache_key in audio_cache:
            print(f"Sử dụng audio cache cho: '{normalized_text[:30]}...'")
            audio_data = audio_cache[cache_key]
            print(f"Kích thước audio từ cache: {len(audio_data)} bytes")
            
            # Chia thành các chunk để stream
            chunk_size = 12288  # Tăng kích thước chunk lên 12KB để gửi nhanh hơn
            chunks_sent = 0
            for i in range(0, len(audio_data), chunk_size):
                chunk = audio_data[i:i+chunk_size]
                print(f"Gửi chunk {chunks_sent+1} từ cache: {len(chunk)} bytes") 
                yield chunk
                chunks_sent += 1
            
            print(f"Đã gửi tổng cộng {chunks_sent} chunks từ cache")
            return
    
    try:
        # Tạo file tạm để lưu audio
        with tempfile.NamedTemporaryFile(delete=False, suffix='.mp3') as temp_file:
            temp_path = temp_file.name
            print(f"Đã tạo file tạm: {temp_path}")
        
        # Tạo audio với giọng tiếng Việt - ưu tiên tốc độ
        start_time = time.time()
        print("Bắt đầu tạo audio với gTTS...")
        
        # Chia nhỏ văn bản nếu quá dài để tránh lỗi
        max_length = 500
        all_audio = b''
        
        # Dùng slow=False để tốc độ đọc nhanh nhất có thể
        use_slow = False
        
        if len(normalized_text) > max_length:
            print(f"Văn bản quá dài ({len(normalized_text)} ký tự), chia nhỏ thành các đoạn")
            text_parts = []
            # Chia theo câu để tránh cắt giữa câu
            sentences = normalized_text.replace('. ', '.|').replace('? ', '?|').replace('! ', '!|').split('|')
            current_part = ""
            
            for sentence in sentences:
                if len(current_part) + len(sentence) < max_length:
                    current_part += sentence + " "
                else:
                    if current_part:
                        text_parts.append(current_part.strip())
                    current_part = sentence + " "
            
            if current_part:
                text_parts.append(current_part.strip())
                
            print(f"Đã chia thành {len(text_parts)} đoạn văn bản")
            
            # Tạo audio cho từng đoạn
            for i, part in enumerate(text_parts):
                part_file = f"{temp_path}_part{i}.mp3"
                print(f"Tạo audio cho phần {i+1}/{len(text_parts)}")
                tts = gTTS(text=part, lang='vi', slow=use_slow)
                tts.save(part_file)
                
                with open(part_file, 'rb') as f:
                    part_audio = f.read()
                    all_audio += part_audio
                    
                try:
                    os.unlink(part_file)
                except:
                    pass
        else:
            print(f"Tạo audio cho văn bản ngắn ({len(normalized_text)} ký tự)")
            tts = gTTS(text=normalized_text, lang='vi', slow=use_slow)
            tts.save(temp_path)
            
            with open(temp_path, 'rb') as f:
                all_audio = f.read()
        
        # Thời gian tạo
        tts_time = time.time() - start_time
        print(f"Đã tạo TTS trong {tts_time:.2f} giây, kích thước: {len(all_audio)} bytes")
        
        # Lưu vào cache nếu kích thước hợp lý
        if len(all_audio) < 1024 * 1024 * 2:  # Giới hạn 2MB
            with cache_lock:
                audio_cache[cache_key] = all_audio
                print(f"Đã lưu vào cache, hiện có {len(audio_cache)} mục trong cache")
        
        # Chia thành các chunk để stream
        chunk_size = 12288  # Tăng kích thước chunk lên 12KB
        chunks_sent = 0
        for i in range(0, len(all_audio), chunk_size):
            chunk = all_audio[i:i+chunk_size]
            print(f"Gửi chunk {chunks_sent+1}: {len(chunk)} bytes") 
            yield chunk
            chunks_sent += 1
            
            # Giảm độ trễ giữa các chunk để stream nhanh hơn
            time.sleep(0.003)  # Giảm xuống 3ms thay vì 5ms
        
        print(f"Đã gửi tổng cộng {chunks_sent} chunks")
        
        # Xóa file tạm
        try:
            os.unlink(temp_path)
            print(f"Đã xóa file tạm: {temp_path}")
        except Exception as e:
            print(f"Lỗi khi xóa file tạm: {str(e)}")
    
    except Exception as e:
        print(f"Lỗi khi tạo giọng nói: {str(e)}")
        yield b''  # Trả về byte rỗng để đảm bảo generator vẫn hoạt động

# Tạo sẵn một số câu trả lời phổ biến khi khởi động
def preload_common_responses():
    """Tạo sẵn một số câu trả lời phổ biến để cache"""
    common_responses = [
        "Xin chào, tôi có thể giúp gì cho bạn?",
        "Tôi không hiểu câu hỏi của bạn. Bạn có thể nói rõ hơn được không?",
        "Xin lỗi, tôi không thể trả lời câu hỏi này.",
        "Cảm ơn bạn đã sử dụng trợ lý giọng nói của chúng tôi.",
        "Vui lòng đợi trong giây lát.",
        "Xin lỗi, tôi sẽ dừng lại và lắng nghe bạn."
    ]
    
    # Tạo audio và cache trong một thread riêng để không chặn khởi động
    def preload_thread():
        for response in common_responses:
            print(f"Bắt đầu tạo sẵn audio cho: '{response}'")
            list(get_text_to_speech(response))
            print(f"Đã tạo sẵn audio cho: '{response}'")
    
    threading.Thread(target=preload_thread, daemon=True).start()
    print("Đã bắt đầu tạo sẵn các câu trả lời phổ biến")

# Khởi động preloading khi import module
print("Khởi tạo module text-to-speech...")
preload_common_responses()

if __name__ == "__main__":
    # Thử nghiệm chức năng text-to-speech
    test_text = "Xin chào, đây là bài kiểm tra giọng nói tiếng Việt."
    print("\n----- Kiểm tra TTS lần 1 -----")
    chunks = list(get_text_to_speech(test_text))
    print(f"Đã tạo {len(chunks)} đoạn âm thanh.")
    
    # Kiểm tra cache
    print("\n----- Kiểm tra TTS lần 2 (từ cache) -----")
    chunks2 = list(get_text_to_speech(test_text))
    print(f"Lần 2: Đã tạo {len(chunks2)} đoạn âm thanh (từ cache).") 