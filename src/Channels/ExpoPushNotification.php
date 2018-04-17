<?php

namespace Levaral\Core\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;

class ExpoPushNotification
{
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toExpoPush($notifiable);

        $content = [];
        if (!isset($message['to'])) {
            foreach ($notifiable->expoTokens() as $token) {
                $content[] = ['to' => $token, 'body' => $message['message']];
            }
        } else {
            $content = $message;
        }
        // Send notification to the $notifiable instance...
        $client = new Client();
        $request = $client->post(self::EXPO_PUSH_URL, [
            'accept' => 'application/json',
            'accept-encoding' => 'gzip, deflate',
            'content-type' => 'application/json',
        ], $content);
//        $request->setBody($message);

        $response = $request->send();
    }
}