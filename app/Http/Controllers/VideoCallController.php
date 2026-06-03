<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class VideoCallController extends Controller
{
    public function index(Request $request)
    {
        $room = $request->query('room', 'test-room');
        $identity = $request->query('identity', 'guest-' . Str::random(5));

        return view('call', [
            'room' => $room,
            'identity' => $identity,
            'livekitUrl' => config('livekit.url'),
        ]);
    }

    public function token(Request $request)
    {
        $room = $request->query('room', 'test-room');
        $identity = $request->query('identity', 'guest-' . Str::random(5));

        $apiKey = config('livekit.api_key');
        $apiSecret = config('livekit.api_secret');

        if (empty($apiKey) || empty($apiSecret)) {
            Log::error('LiveKit credentials are missing when generating token.');
            return Response::json([
                'error' => 'LiveKit configuration is missing. Please set LIVEKIT_API_KEY and LIVEKIT_API_SECRET.',
            ], 500);
        }

        $now = time();
        $payload = [
            'jti' => Str::random(16) . '-' . $now,
            'iss' => $apiKey,
            'sub' => $identity,
            'aud' => 'livekit',
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 300,
            'video' => [
                'room' => $room,
                'roomJoin' => true,
            ],
            'name' => $identity,
            'metadata' => json_encode([
                'generated_by' => 'Laravel LiveKit demo',
            ]),
        ];

        Log::info('Generating LiveKit token', ['payload' => $payload]);
        Log::info('LiveKit API Secret', ['api_secret' => $apiSecret]);
        $token = JWT::encode($payload, $apiSecret, 'HS256');

        return Response::json([
            'token' => $token,
            'room' => $room,
            'identity' => $identity,
            'livekit_url' => config('livekit.url'),
        ]);
    }
}
