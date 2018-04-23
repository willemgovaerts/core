<?php

namespace Levaral\Core;

use Illuminate\Notifications\Notification;
use App\Domain\MailLog\MailLog;

class Util
{
    public static function notify($notifiable, Notification $notification, $model = null)
    {
        $mailLog = new MailLog();
        $mailLog->mail_type = substr(get_class($notification), strrpos(get_class($notification), '\\') + 1);
        $mailLog->save();

        if ($model) {
            $mailLog = $model->mailLogs()->save($mailLog);
        }

        $notifiable->model_id = $mailLog->id;
        $notifiable->notify($notification);
    }
}