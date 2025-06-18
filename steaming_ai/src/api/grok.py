import os
import groq
import datetime
from typing import List, Dict, Optional

# Biến toàn cục để lưu lịch sử trò chuyện
conversation_history: List[Dict[str, str]] = []

def get_ai_response(user_input: str, use_history: bool = True, max_history: int = 5) -> str:
    """
    Lấy phản hồi từ API mô hình ngôn ngữ Groq.
    
    Args:
        user_input (str): Đầu vào của người dùng
        use_history (bool): Có sử dụng lịch sử trò chuyện không
        max_history (int): Số lượng trao đổi tối đa để bao gồm
        
    Returns:
        str: Văn bản phản hồi của AI
    """
    global conversation_history
    
    try:
        api_key = os.getenv("GROQ_API_KEY")
        if not api_key:
            return "Lỗi: Không tìm thấy GROQ_API_KEY trong biến môi trường."
        
        client = groq.Client(api_key=api_key)
        
        # Thêm ngày giờ hiện tại để AI có thông tin thời gian
        current_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        # System prompt định nghĩa vai trò và khả năng của trợ lý
        system_prompt = f"""Bạn là một trợ lý AI thông minh, nói tiếng Việt lưu loát với giọng điệu thân thiện và tự nhiên. 
Hôm nay là {current_time}, và bạn luôn cung cấp thông tin mới nhất mà bạn biết.

Hãy tuân thủ các nguyên tắc sau:

1. Trả lời ngắn gọn, rõ ràng và hữu ích, phù hợp với giao tiếp bằng giọng nói.
2. Sử dụng ngôn ngữ tự nhiên, dễ hiểu, giống như một người Việt Nam đang nói chuyện.
3. Khi người dùng hỏi về thông tin, cung cấp thông tin chính xác, cập nhật và hữu ích.
4. Có thể giúp đỡ với nhiều loại yêu cầu: trả lời câu hỏi, cung cấp định nghĩa, tính toán đơn giản, gợi ý, v.v.
5. Thể hiện sự lịch sự, tôn trọng và thân thiện trong mọi tương tác.
6. Không trả lời các nội dung gây hại, phân biệt đối xử, không phù hợp hoặc vi phạm đạo đức.
7. Nếu không biết câu trả lời, hãy thừa nhận và đề xuất cách tiếp cận thay thế.
8. Trả lời bằng tiếng Việt, trừ khi người dùng yêu cầu ngôn ngữ khác.

Các lĩnh vực chuyên môn của bạn bao gồm:
- Tin tức và thời sự hiện tại (dựa trên kiến thức được cập nhật của bạn)
- Giáo dục và kiến thức chung
- Khoa học, công nghệ và máy tính
- Sức khỏe và y tế (lưu ý bạn không phải bác sĩ)
- Văn hóa, nghệ thuật và giải trí
- Thể thao và sự kiện
- Kinh doanh và tài chính
- Du lịch và địa điểm
- Lịch sử và địa lý
- Ẩm thực và nấu ăn

Bạn có thể thực hiện các tác vụ sau:
- Trả lời câu hỏi và cung cấp thông tin
- Giải thích khái niệm và định nghĩa
- Cung cấp hướng dẫn và quy trình từng bước
- Chuyển đổi đơn vị và tính toán đơn giản
- Đề xuất ý tưởng và gợi ý
- Cung cấp tóm tắt và phân tích thông tin
- Trợ giúp ngôn ngữ: dịch, giải thích thành ngữ, v.v.

Hãy cố gắng trở thành một trợ lý AI hữu ích, thân thiện và đáng tin cậy."""

        # Tạo danh sách messages để gửi đến API
        messages = [{"role": "system", "content": system_prompt}]
        
        # Thêm lịch sử trò chuyện nếu được yêu cầu
        if use_history and conversation_history:
            # Chỉ lấy max_history cuộc trao đổi gần nhất
            recent_history = conversation_history[-max_history:] if len(conversation_history) > max_history else conversation_history
            messages.extend(recent_history)
        
        # Thêm tin nhắn hiện tại của người dùng
        messages.append({"role": "user", "content": user_input})
        
        # Gọi API Groq với đầu vào của người dùng
        response = client.chat.completions.create(
            model="llama3-70b-8192",  # Sử dụng mô hình Llama 3 70B
            messages=messages,
            temperature=0.7,
            max_tokens=2000,
            stream=False
        )
        
        # Trích xuất phản hồi của trợ lý
        ai_response = response.choices[0].message.content
        
        # Cập nhật lịch sử trò chuyện
        if use_history:
            conversation_history.append({"role": "user", "content": user_input})
            conversation_history.append({"role": "assistant", "content": ai_response})
        
        return ai_response
    
    except Exception as e:
        print(f"Lỗi trong quá trình tạo phản hồi AI: {str(e)}")
        return f"Xin lỗi, tôi đã gặp lỗi khi xử lý yêu cầu của bạn: {str(e)}"

def clear_conversation_history():
    """Xóa toàn bộ lịch sử cuộc trò chuyện"""
    global conversation_history
    conversation_history = []
    return "Lịch sử cuộc trò chuyện đã được xóa."

if __name__ == '__main__':
    # Example usage: requires a valid API key in .env file
    if not os.getenv("GROQ_API_KEY"):
        print("Vui lòng đặt GROQ_API_KEY trong file .env để kiểm tra.")
    else:
        response = get_ai_response("Xin chào, bạn là ai?")
        print(f"Phản hồi AI: {response}")
        
        # Kiểm tra lịch sử cuộc trò chuyện
        response = get_ai_response("Tôi vừa hỏi gì vậy?")
        print(f"Phản hồi AI: {response}") 