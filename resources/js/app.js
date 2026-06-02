import {
    Room,
    RoomEvent,
    VideoPresets,
    Track
} from 'livekit-client';

// Получаем элементы DOM
const joinButton = document.getElementById('joinButton');
const leaveButton = document.getElementById('leaveButton');
const statusEl = document.getElementById('status');
const localVideo = document.getElementById('localVideo');
const remoteTracksContainer = document.getElementById('remoteTracks');

let currentRoom = null;

const setStatus = (msg) => { if (statusEl) statusEl.textContent = msg; };

async function connectToCall() {
    const roomName = document.getElementById('room').value.trim();
    const identity = document.getElementById('identity').value.trim();
    const livekitUrl = document.getElementById('livekitConfig').value;

    if (!roomName || !identity) {
        alert('Введите имя комнаты и ваше имя');
        return;
    }

    setStatus('Получение токена...');

    try {
        // 1. Запрашиваем токен у вашего Laravel контроллера
        const response = await fetch(`/call/token?room=${encodeURIComponent(roomName)}&identity=${encodeURIComponent(identity)}`);
        const data = await response.json();

        if (data.error) throw new Error(data.error);

        // 2. Создаем объект комнаты
        currentRoom = new Room({
            adaptiveStream: true,
            dynacast: true,
            videoCaptureDefaults: {
                resolution: VideoPresets.h720.resolution,
            },
        });

        // 3. Настраиваем события для удаленных участников
        currentRoom.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            if (track.kind === Track.Kind.Video || track.kind === Track.Kind.Audio) {
                const element = track.attach();
                element.style.width = '100%';
                element.style.borderRadius = '0.75rem';
                element.id = `track-${participant.identity}-${track.kind}`;
                remoteTracksContainer.appendChild(element);
            }
        });

        currentRoom.on(RoomEvent.TrackUnsubscribed, (track, publication, participant) => {
            track.detach().forEach(el => el.remove());
            const el = document.getElementById(`track-${participant.identity}-${track.kind}`);
            if (el) el.remove();
        });

        // 4. Подключаемся к серверу
        await currentRoom.connect(livekitUrl, data.token);
        setStatus(`Подключено к ${roomName}`);

        joinButton.disabled = true;
        leaveButton.disabled = false;

        // 5. Публикуем свои потоки
        // 5. Публикуем свои потоки
        // Этот метод включает и камеру, и микрофон
        await currentRoom.localParticipant.enableCameraAndMicrophone();

        // 6. Показываем свою камеру локально
        // Используем новый способ получения видео-трека
        const videoPublication = currentRoom.localParticipant.getTrackPublication(Track.Source.Camera);

        if (videoPublication && videoPublication.videoTrack) {
            // attach() может принимать существующий элемент <video> или создавать новый
            // Если localVideo — это тег <video>, то просто:
            videoPublication.videoTrack.attach(localVideo);
        } else {
            console.warn("Видео-трек не найден или не был опубликован");
        }

    } catch (error) {
        console.error('LiveKit Error:', error);
        setStatus('Ошибка: ' + error.message);
    }
}

async function leaveCall() {
    if (currentRoom) {
        await currentRoom.disconnect();
        currentRoom = null;
    }

    // Очистка UI
    localVideo.srcObject = null;
    remoteTracksContainer.innerHTML = '';
    joinButton.disabled = false;
    leaveButton.disabled = true;
    setStatus('Вы вышли из комнаты');
}

// Привязываем функции к кнопкам
if (joinButton) joinButton.addEventListener('click', connectToCall);
if (leaveButton) leaveButton.addEventListener('click', leaveCall);
