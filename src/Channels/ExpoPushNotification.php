<?php

namespace Levaral\Core\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;

class ExpoPushNotification
{
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';
    const SUCCESS_STATUS_CODE = 200;

    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toExpo($notifiable);
        $content = [];

        if (!isset($message['to'])) {
            foreach ($notifiable->expoTokens() as $token) {
                $content[] = array_merge(['to' => $token], $message);
            }
        } else {
            $content = $message;
        }

        // Send notification to the $notifiable instance...
        $client = new Client();

        $request = $client->post(self::EXPO_PUSH_URL, [
            'headers' => [
                'accept' => 'application/json',
                'accept-encoding' => 'gzip, deflate',
                'content-type' => 'application/json',
            ],
            'json' => $content
        ], $content);

        $request->send();

        if($request->getStatusCode() !== SUCCESS_STATUS_CODE) {
            throw new \Exception('Could not process expo push notification request');
        }
    }
}