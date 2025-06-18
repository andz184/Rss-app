document.addEventListener('DOMContentLoaded', () => {
    const socket = io();

    const toggleBtn = document.getElementById('toggle-conversation-btn');
    const conversationArea = document.getElementById('conversation-area');
    const statusIndicator = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');
    const audioPlayer = document.getElementById('audio-player');
    const useHistoryToggle = document.getElementById('use-history-toggle');
    const clearHistoryBtn = document.getElementById('clear-history-btn');
    const volumeSlider = document.getElementById('volume-slider');
    const volumeValue = document.getElementById('volume-value');

    let isConversationActive = false;
    let audioChunks = [];
    let isPlaying = false;
    let silenceTimeout = null;
    let lastTranscript = '';
    let isSpeaking = false;
    let recognitionRestarts = 0;
    let assistantIsSpeaking = false;
    const MAX_RESTARTS = 5;
    
    // Biến quản lý triệt echo
    let lastAudioEndTime = 0;
    let echoProtectionActive = false;
    const ECHO_THRESHOLD_MS = 1200; // Ngưỡng triệt echo 1.2 giây
    
    // Thiết lập âm lượng mặc định để giảm echo
    if (audioPlayer) {
        // Thiết lập âm lượng mặc định từ trình trượt
        audioPlayer.volume = volumeSlider.value;
        volumeValue.textContent = `${Math.round(volumeSlider.value * 100)}%`;
    }
    
    // Cập nhật âm lượng khi người dùng điều chỉnh
    volumeSlider.addEventListener('input', () => {
        const newVolume = volumeSlider.value;
        audioPlayer.volume = newVolume;
        volumeValue.textContent = `${Math.round(newVolume * 100)}%`;
        console.log(`Đã đặt âm lượng: ${Math.round(newVolume * 100)}%`);
    });

    // Speech Recognition setup
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert("Trình duyệt của bạn không hỗ trợ Web Speech API. Vui lòng sử dụng Chrome hoặc Edge.");
        return;
    }
    
    // Tạo một đối tượng nhận diện giọng nói mới
    function createRecognition() {
        const recognition = new SpeechRecognition();
        recognition.lang = 'vi-VN'; // Tiếng Việt
        recognition.interimResults = true; // Cho phép kết quả tạm thời
        recognition.continuous = true; // Liên tục lắng nghe
        recognition.maxAlternatives = 3; // Tăng số lượng phương án thay thế
        
        // Giảm ngưỡng phát hiện im lặng để nhạy hơn
        recognition.interimResults = true;
        
        // Xử lý sự kiện bắt đầu nhận diện
        recognition.onstart = () => {
            updateStatus('listening', 'Đang lắng nghe...');
            isSpeaking = false;
            console.log('Đã bắt đầu nhận diện giọng nói');
        };
        
        // Xử lý kết quả nhận diện
        recognition.onresult = (event) => {
            clearTimeout(silenceTimeout);
            
            // Kiểm tra bảo vệ echo - bỏ qua âm thanh nhận được ngay sau khi trợ lý nói xong
            if (echoProtectionActive) {
                const timeSinceAudioEnd = Date.now() - lastAudioEndTime;
                if (timeSinceAudioEnd < ECHO_THRESHOLD_MS) {
                    console.log(`Bỏ qua âm thanh có thể là echo (${timeSinceAudioEnd}ms sau khi trợ lý nói xong)`);
                    return;
                } else {
                    // Tắt bảo vệ echo sau khi qua ngưỡng thời gian
                    echoProtectionActive = false;
                }
            }
            
            let finalTranscript = '';
            let interimTranscript = '';
            
            // Xử lý tất cả các kết quả từ sự kiện
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript.trim();
                if (event.results[i].isFinal) {
                    finalTranscript += transcript + ' ';
                } else {
                    interimTranscript += transcript;
                }
            }
            
            // Hiển thị dấu hiệu đang nghe khi có tiếng nói
            if (interimTranscript && !isSpeaking) {
                isSpeaking = true;
                updateStatus('active-listening', 'Đang nghe bạn nói...');
                console.log('Phát hiện tiếng nói: ' + interimTranscript);
                
                // Nếu trợ lý đang nói, gửi tín hiệu ngắt
                if (assistantIsSpeaking) {
                    console.log('Ngắt lời trợ lý vì phát hiện người dùng đang nói');
                    stopAssistantSpeech();
                }
            }
            
            // Gửi văn bản cuối cùng nếu có
            if (finalTranscript) {
                addMessage('user', finalTranscript);
                lastTranscript = finalTranscript;
                updateStatus('processing', 'Đang xử lý...');
                socket.emit('user_speech', { 
                    text: finalTranscript,
                    use_history: useHistoryToggle.checked
                });
                isSpeaking = false;
            }
            
            // Thiết lập timer phát hiện im lặng
            silenceTimeout = setTimeout(() => {
                if (isSpeaking && interimTranscript) {
                    // Gửi văn bản tạm thời nếu người dùng ngừng nói
                    addMessage('user', interimTranscript);
                    updateStatus('processing', 'Đang xử lý...');
                    socket.emit('user_speech', { 
                        text: interimTranscript,
                        use_history: useHistoryToggle.checked
                    });
                    console.log('Phát hiện im lặng, gửi: ' + interimTranscript);
                }
                isSpeaking = false;
                updateStatus('listening', 'Đang lắng nghe...');
            }, 1200); // 1.2 giây im lặng
        };
        
        // Xử lý khi nhận diện kết thúc
        recognition.onend = () => {
            console.log('Nhận diện giọng nói đã kết thúc');
            if (isConversationActive) {
                if (recognitionRestarts < MAX_RESTARTS) {
                    // Khởi động lại nhận diện với thời gian chờ ngắn
                    setTimeout(() => {
                        try {
                            recognition.start();
                            recognitionRestarts++;
                            console.log(`Khởi động lại lần ${recognitionRestarts}/${MAX_RESTARTS}`);
                        } catch (e) {
                            console.error('Lỗi khi khởi động lại nhận diện giọng nói', e);
                        }
                    }, 200);
                } else {
                    // Tạo lại đối tượng nhận diện nếu đã khởi động lại quá nhiều lần
                    console.log('Tạo lại đối tượng nhận diện giọng nói sau nhiều lần khởi động lại');
                    recognitionRestarts = 0;
                    currentRecognition = createRecognition();
                    currentRecognition.start();
                }
            } else {
                updateStatus('idle', 'Đã dừng');
            }
        };
        
        // Xử lý lỗi nhận diện
        recognition.onerror = (event) => {
            console.error('Lỗi nhận diện giọng nói:', event.error);
            if (event.error === 'no-speech') {
                // Bình thường, không làm gì
            } else if (event.error === 'audio-capture') {
                updateStatus('error', 'Không thể truy cập microphone');
                addMessage('system', 'Không thể truy cập microphone. Vui lòng kiểm tra quyền truy cập.');
            } else if (event.error === 'not-allowed') {
                updateStatus('error', 'Không được phép sử dụng microphone');
                addMessage('system', 'Bạn đã từ chối quyền truy cập microphone.');
            } else if (event.error === 'network') {
                updateStatus('error', 'Lỗi kết nối mạng');
            } else if (event.error === 'aborted') {
                // Bị hủy, thường là do người dùng hủy
            } else {
                updateStatus('error', 'Lỗi. Đang khởi động lại...');
            }
        };
        
        // Trả về đối tượng đã cấu hình
        return recognition;
    }
    
    // Tạo đối tượng nhận diện ban đầu
    let currentRecognition = createRecognition();
    
    // Dừng trợ lý đang nói
    function stopAssistantSpeech() {
        if (assistantIsSpeaking) {
            socket.emit('interrupt_speech');
            console.log('Đã gửi tín hiệu ngắt lời trợ lý');
            stopAudio();
        }
    }
    
    // Dừng phát âm thanh
    function stopAudio() {
        try {
            if (audioPlayer) {
                audioPlayer.pause();
                audioPlayer.src = '';
            }
            audioChunks = [];
            isPlaying = false;
            assistantIsSpeaking = false;
            console.log('Đã dừng audio đang phát');
        } catch (e) {
            console.error('Lỗi khi dừng audio:', e);
        }
    }
    
    toggleBtn.addEventListener('click', () => {
        isConversationActive = !isConversationActive;
        if (isConversationActive) {
            toggleBtn.textContent = 'Dừng Hội Thoại';
            toggleBtn.classList.add('recording');
            try {
                // Khởi động lại từ đầu với đối tượng mới
                if (currentRecognition) {
                    try { currentRecognition.stop(); } catch(e) {}
                }
                recognitionRestarts = 0;
                currentRecognition = createRecognition();
                currentRecognition.start();
                addMessage('system', 'Hội thoại đã bắt đầu. Bạn có thể nói ngay bây giờ.');
            } catch (e) {
                console.error('Lỗi khi bắt đầu nhận diện giọng nói:', e);
                addMessage('system', 'Lỗi khi bắt đầu nhận diện giọng nói. Vui lòng làm mới trang.');
            }
        } else {
            toggleBtn.textContent = 'Bắt Đầu Hội Thoại';
            toggleBtn.classList.remove('recording');
            try {
                currentRecognition.stop();
            } catch (e) {
                console.error('Lỗi khi dừng nhận diện giọng nói:', e);
            }
            updateStatus('idle', 'Đã dừng');
            addMessage('system', 'Hội thoại đã dừng.');
            
            // Dừng bất kỳ âm thanh nào đang phát
            stopAudio();
        }
    });
    
    // Xử lý sự kiện xóa lịch sử trò chuyện
    clearHistoryBtn.addEventListener('click', () => {
        socket.emit('clear_history');
        addMessage('system', 'Đang xóa lịch sử trò chuyện...');
    });
    
    // Thông báo khi lịch sử đã được xóa
    socket.on('history_cleared', (data) => {
        if (data.status === 'success') {
            addMessage('system', data.message || 'Lịch sử trò chuyện đã được xóa.');
        }
    });
    
    // Xử lý thay đổi trạng thái sử dụng lịch sử
    useHistoryToggle.addEventListener('change', () => {
        const message = useHistoryToggle.checked ? 
            'Đã bật sử dụng lịch sử trò chuyện.' : 
            'Đã tắt sử dụng lịch sử trò chuyện.';
        addMessage('system', message);
    });
    
    // Nhận thông báo về phát hiện echo
    socket.on('echo_detected', (data) => {
        console.log('Server phát hiện echo:', data.message);
        // Không hiển thị thông báo cho người dùng để tránh làm gián đoạn cuộc trò chuyện
    });

    // Lắng nghe phản hồi văn bản từ trợ lý
    socket.on('assistant_response', (data) => {
        if (data.text && data.text.trim() !== '') {
            addMessage('assistant', data.text);
            // Xóa các chunk âm thanh trước đó khi nhận phản hồi mới
            audioChunks = [];
        }
    });

    // Nhận thông báo trạng thái xử lý
    socket.on('processing_status', (data) => {
        if (data.status) {
            updateStatus('processing', data.status);
        }
    });
    
    // Xử lý lệnh dừng audio
    socket.on('stop_audio', () => {
        console.log('Nhận lệnh dừng phát âm thanh từ server');
        stopAudio();
    });

    // Nhận thông báo lỗi
    socket.on('error', (data) => {
        updateStatus('error', 'Đã xảy ra lỗi');
        addMessage('system', `Lỗi: ${data.message}`);
    });

    // Nhận chunk âm thanh từ trợ lý
    socket.on('assistant_audio_chunk', (data) => {
        try {
            console.log('Đã nhận chunk âm thanh:', data.chunk ? data.chunk.length : 0, 'bytes');
            assistantIsSpeaking = true;
            let audioData;
            
            // Xử lý dữ liệu dựa trên định dạng
            if (data.format === 'base64') {
                // Chuyển đổi từ base64 thành binary
                console.log('Xử lý dữ liệu dạng base64');
                const binary = atob(data.chunk);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) {
                    bytes[i] = binary.charCodeAt(i);
                }
                audioData = bytes;
            } else {
                // Xử lý dữ liệu nhị phân thông thường
                console.log('Xử lý dữ liệu dạng binary');
                audioData = new Uint8Array(Object.values(data.chunk));
            }
            
            console.log('Chunk sau khi chuyển đổi:', audioData.length, 'bytes');
            
            // Tạo blob audio
            const blob = new Blob([audioData], { type: 'audio/mpeg' });
            audioChunks.push(blob);
            
            // Bắt đầu phát ngay khi nhận được chunk đầu tiên
            if (audioChunks.length === 1 && !isPlaying) {
                updateStatus('playing', 'Đang nói...');
                playAudio();
            }
        } catch (error) {
            console.error('Lỗi khi xử lý chunk âm thanh:', error);
        }
    });

    // Xử lý khi kết thúc audio
    socket.on('audio_end', () => {
        console.log('Đã hoàn thành stream âm thanh');
        assistantIsSpeaking = false;
        
        // Bật chế độ bảo vệ echo
        lastAudioEndTime = Date.now();
        echoProtectionActive = true;
        console.log('Đã bật chế độ bảo vệ echo');
        
        // Kiểm tra nếu cần phát các chunk còn lại
        if (!isPlaying && audioChunks.length > 0) {
            playAudio();
        }
        
        // Đặt thời gian chờ để cập nhật trạng thái sau khi đảm bảo tất cả âm thanh đã phát
        setTimeout(() => {
            if (!isPlaying) {
                updateStatus('listening', 'Đang lắng nghe...');
            }
        }, 300);
    });

    // Phát âm thanh từ các chunk đã nhận
    function playAudio() {
        if (audioChunks.length === 0 || isPlaying) {
            console.log('Không thể phát audio: không có chunk hoặc đang phát rồi');
            return;
        }
        
        isPlaying = true;
        assistantIsSpeaking = true;
        updateStatus('playing', 'Đang nói...');
        
        // Kết hợp tất cả các chunk đã có
        const audioBlob = new Blob(audioChunks, { type: 'audio/mpeg' });
        console.log('Đã tạo audioBlob kích thước:', audioBlob.size, 'bytes');
        audioChunks = []; // Xóa hàng đợi
        
        const audioUrl = URL.createObjectURL(audioBlob);
        console.log('Đã tạo audioUrl:', audioUrl);
        audioPlayer.src = audioUrl;
        
        // Áp dụng âm lượng hiện tại
        audioPlayer.volume = volumeSlider.value;
        
        // Thêm event listener để debug
        audioPlayer.onloadedmetadata = () => {
            console.log('Audio đã tải metadata, thời lượng:', audioPlayer.duration, 'giây');
        };
        
        audioPlayer.oncanplay = () => {
            console.log('Audio sẵn sàng để phát');
        };
        
        audioPlayer.onplay = () => {
            console.log('Audio bắt đầu phát');
        };
        
        audioPlayer.onerror = (e) => {
            console.error('Lỗi audio player:', audioPlayer.error);
        };
        
        audioPlayer.onended = () => {
            console.log('Audio đã phát xong');
            isPlaying = false;
            assistantIsSpeaking = false;
            URL.revokeObjectURL(audioUrl);
            
            // Kiểm tra nếu nhận được thêm chunk trong khi đang phát
            if (audioChunks.length > 0) {
                console.log('Có', audioChunks.length, 'chunk mới, tiếp tục phát');
                // Phát lô âm thanh tiếp theo
                playAudio();
            } else {
                // Không còn âm thanh để phát
                console.log('Không còn chunk nào, trở về trạng thái lắng nghe');
                updateStatus('listening', 'Đang lắng nghe...');
            }
        };
        
        // Bắt đầu phát
        console.log('Thử phát audio...');
        audioPlayer.play().then(() => {
            console.log('Đang phát audio thành công');
        }).catch(error => {
            console.error('Lỗi khi phát âm thanh:', error);
            isPlaying = false;
            assistantIsSpeaking = false;
            updateStatus('listening', 'Đang lắng nghe...');
        });
    }
    
    // Thêm tin nhắn vào khu vực hội thoại
    function addMessage(sender, text) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', sender);
        
        let displayName = sender;
        switch(sender) {
            case 'user': displayName = 'Bạn'; break;
            case 'assistant': displayName = 'Trợ lý'; break;
            case 'system': displayName = 'Hệ thống'; break;
        }
        
        messageElement.innerHTML = `<span>${displayName}:</span> ${text}`;
        conversationArea.appendChild(messageElement);
        conversationArea.scrollTop = conversationArea.scrollHeight;
    }

    // Cập nhật trạng thái hiển thị
    function updateStatus(state, text) {
        statusIndicator.className = `status-indicator-${state}`;
        statusText.textContent = text;
    }
}); 