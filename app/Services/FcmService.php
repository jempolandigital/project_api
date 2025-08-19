<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;

class FcmService
{
    protected static function getAccessToken()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/firebase-key.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        return $token['access_token'];
    }

    public static function sendNotification($fcmToken, $title, $body, $data = [])
    {
        $projectId = env('FIREBASE_PROJECT_ID'); // isi di .env
        $accessToken = self::getAccessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $response = Http::withToken($accessToken)
            ->post($url, [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $data,
                ],
            ]);

        return $response->json();
    }


     // 🔹 Tambahkan ini biar support array token
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        $responses = [];
        foreach ($tokens as $token) {
            $responses[] = self::sendNotification($token, $title, $body, $data);
        }
        return $responses;
    }
}

