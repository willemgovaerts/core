<?php

namespace Levaral\Core;


use App\Domain\MailTemplate\MailTemplate;
use Illuminate\Notifications\Notification;
use App\Domain\MailLog\MailLog;
use Illuminate\Notifications\Messages\MailMessage;

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

    public static function getTemplate($notification, $notifiable, $locale = 'en')
    {
        // Get notification class name from the object.
        $notificationPath = get_class($notification);
        $notificationPathArray = explode("\\", $notificationPath);
        $notificationType = end($notificationPathArray);

        $mailTemplate = MailTemplate::query()->with(['content'])->where('type', $notificationType)->first();

        $mailContent = $mailTemplate->content->where('locale', $locale)->first();

        $mailTemplateVariables = $notification->getTemplateVariables($notifiable);

        // Global variables assignment
        $mailTemplateVariables['siteLink'] = env('APP_URL');
        $mailTemplateVariables['loinLink'] = '#';
        $mailTemplateVariables['registerLink'] = '#';
        $mailTemplateVariables['username'] = '';
        $mailTemplateVariables['name'] = '';
        $mailTemplateVariables['email'] = '';

        $templateContent = $mailContent->content;

        foreach ($mailTemplateVariables as $mailTemplateVariable=>$val) {
            $templateContent = str_replace('[' . $mailTemplateVariable . ']', $val, $templateContent);
        }

        //return $templateContent;
        return (new MailMessage)
            ->subject($mailContent->subject)
            ->view('vendor.notifications.email', compact('templateContent'));
    }
}