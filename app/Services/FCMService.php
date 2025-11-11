<?php

namespace App\Services;

use Google_Client;
use Illuminate\Support\Facades\Http;

class FCMService
{
    protected $keyFilePath;
    protected $client;

    public function __construct()
    {
        $this->keyFilePath = storage_path('app/myvoice-e7148-firebase-adminsdk-tyull-8b21a0d38e.json');
        $this->client = new Google_Client();
        $this->client->setAuthConfig($this->keyFilePath);
        $this->client->addScope('https://www.googleapis.com/auth/cloud-platform');
    }

    public function getAccessToken(): string
    {
        $token = $this->client->fetchAccessTokenWithAssertion();
        return $token['access_token'] ?? '';
    }

    public function sendMessage(string $deviceToken, array $notification, array $data = [])
    {
        $accessToken = $this->getAccessToken();
        $projectId = 'myvoice-e7148';
        $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'message' => [
                'token' => $deviceToken,
                'notification' => $notification,
                'data' => $data,
            ],
        ]);

        return $response->json();
    }

    public function sendMessages(array $tokens, array $notification, array $data = [])
    {
        $accessToken = $this->getAccessToken();
        $projectId = 'myvoice-e7148';
        $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

        // Using Http::pool to send requests concurrently
        $responses = Http::pool(function ($pool) use ($tokens, $notification, $data, $url, $accessToken) {
            foreach ($tokens as $token) {
                $pool->as($token)->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'message' => [
                        'token' => $token,
                        'notification' => $notification,
                        'data' => $data,
                    ],
                ]);
            }
        });

        // Return JSON responses
        $results = [];
        foreach ($responses as $token => $response) {
            $results[$token] = $response->json();
        }

        return $results;
    }
}
