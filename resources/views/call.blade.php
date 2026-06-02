<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>LiveKit Video Call</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #f3f4f6; color: #111827; }
        .page { min-height: 100vh; display: flex; flex-direction: column; gap: 1rem; padding: 1.5rem; }
        .panel { background: #ffffff; border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 1.5rem; }
        .video-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); }
        video { width: 100%; border-radius: 0.75rem; background: #000; object-fit: cover; }
        button, input { font: inherit; }
        .controls { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; }
        .button { border: none; border-radius: 999px; background: #2563eb; color: #fff; padding: 0.9rem 1.25rem; cursor: pointer; transition: background .2s ease; }
        .button:disabled { opacity: 0.55; cursor: not-allowed; }
        .button.secondary { background: #6b7280; }
        .status { margin-top: 0.5rem; color: #374151; }
        .footer { margin-top: auto; font-size: 0.9rem; color: #6b7280; }
        .input { border: 1px solid #d1d5db; border-radius: 999px; padding: 0.8rem 1rem; width: 100%; max-width: 320px; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Скрытое поле для передачи конфига в JS -->
        <input type="hidden" id="livekitConfig" value="{{ $livekitUrl }}">

        <div class="panel">
            <h1 style="margin:0 0 0.75rem 0;">LiveKit Test Video Call</h1>
            <p style="margin:0 0 1rem 0; max-width: 42rem;">Используется NPM пакет livekit-client. Все управление перенесено в app.js.</p>

            <div class="controls">
                <input id="room" class="input" placeholder="Room name" value="{{ $room }}" />
                <input id="identity" class="input" placeholder="Your name" value="{{ $identity }}" />
                <button id="joinButton" class="button">Join Room</button>
                <button id="leaveButton" class="button secondary" disabled>Leave</button>
            </div>

            <div class="status" id="status">Ожидание подключения...</div>
        </div>

        <div class="panel video-grid">
            <div>
                <h2 style="margin:0 0 0.5rem 0;">Local camera</h2>
                <video id="localVideo" autoplay muted playsinline></video>
            </div>
            <div>
                <h2 style="margin:0 0 0.5rem 0;">Remote participants</h2>
                <div id="remoteTracks" style="display:grid; gap:1rem;"></div>
            </div>
        </div>

        <div class="footer">Laravel + Vite + LiveKit</div>
    </div>

    @vite('resources/js/app.js')
</body>
</html>
