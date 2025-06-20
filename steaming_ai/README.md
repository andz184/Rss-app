# Steaming AI Chatbot

This is a real-time conversational AI chatbot with a graphical user interface. It uses speech recognition to capture user input, a language model to generate responses, and text-to-speech to voice the AI's answers.

## Features

-   Real-time conversation with a voice-based AI.
-   Continuous speech recognition with silence detection.
-   Text and voice responses from the AI.
-   GUI to display the conversation.
-   Streaming audio for AI's voice.

## Setup and Installation

### 1. Prerequisites

-   Python 3.7+
-   A virtual environment (recommended).

### 2. Installation

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/steaming-ai.git
    cd steaming-ai
    ```

2.  **Create and activate a virtual environment:**

    On Windows:
    ```bash
    python -m venv venv
    .\venv\Scripts\activate
    ```

    On macOS/Linux:
    ```bash
    python3 -m venv venv
    source venv/bin/activate
    ```

3.  **Install the required packages:**

    ```bash
    pip install -r requirements.txt
    ```

### 3. API Key Configuration

You need to configure API keys for the AI model (Grok) and the text-to-speech service (ElevenLabs).

1.  **Create a `.env` file** in the root directory of the project.
2.  **Add your API keys** to the `.env` file as follows:

    ```
    GROQ_API_KEY="your_grok_api_key"
    ELEVENLABS_API_KEY="your_elevenlabs_api_key"
    ```

    -   Get your Grok API key from the [Grok website](https://console.groq.com/keys).
    -   Get your ElevenLabs API key from the [ElevenLabs website](https://elevenlabs.io/).

## How to Run the Application

Once you have completed the setup and configuration, you can run the application with the following command:

```bash
python main.py
```

The application window will open, and you can start the conversation by clicking the "Start Conversation" button.

## How it Works

1.  **GUI (Tkinter)**: The user interface is built with Tkinter, providing a simple window with a text area for the conversation and buttons to control the chat.
2.  **Speech Recognition**: The `SpeechRecognition` library, along with `PyAudio`, is used to capture audio from the microphone. It is configured to detect when the user stops speaking.
3.  **AI Response Generation**: The transcribed text is sent to the Grok API, which returns a text-based response.
4.  **Text-to-Speech**: The response from the AI is sent to the ElevenLabs API, which generates a natural-sounding voice. The audio is streamed back and played in real-time.
5.  **Conversation Flow**: The conversation is displayed in the GUI, showing both the user's questions and the AI's answers.

## Customization

You can customize the following parameters in the code:

-   **Silence Threshold**: Adjust the `energy_threshold` and `pause_threshold` in `src/audio_recorder.py` to change the sensitivity of the silence detection.
-   **Voice Selection**: You can change the voice used by ElevenLabs by modifying the `voice_id` in `src/api/elevenlabs.py`.
-   **AI Model**: You can switch to a different language model by changing the API endpoint and request format in `src/api/grok.py`.

## Alternative Libraries and Improvements

-   **GUI**: For a more modern look, you could use libraries like `PyQt`, `PySide`, or a web-based framework like `Flask` or `Django` with a WebSocket for real-time communication.
-   **Offline Speech Recognition**: For offline capabilities, you can use models like `Vosk` or `CMU Sphinx`.
-   **Performance**: For better performance, you could implement more sophisticated audio processing and streaming techniques. #   A I _ s t r e a m i n g _ a u d i o  
 