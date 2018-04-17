<?php
namespace Levaral\Core\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;

class ExpoPushNotificationChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toExpoPush($notifiable);

        // Send notification to the $notifiable instance...
        $client = new Client();
        $request = $client->post('https://exp.host/--/api/v2/push/send',array(
            'accept'=>'application/json',
            'accept-encoding'=>'gzip, deflate',
            'content-type' => 'application/json',
        ),array());
        $request->setBody($notifiable);
        
        $response = $request->send();

        $message = $notification->toPushExpoNotification($notifiable);
    }
}