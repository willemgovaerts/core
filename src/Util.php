<?php

namespace Levaral\Core;


use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;
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

    /**
     * @param $notification
     * @param $notifiable
     * @param string $locale
     * @return mixed
     */
    public static function getMailTemplate($notification, $notifiable, $locale = 'en')
    {
        // Get notification class name from the object.
        $notificationPath = get_class($notification);
        $notificationClassName = class_basename($notificationPath);

        $mailTemplate = MailTemplate::query()->where('type', $notificationClassName)->first();

        $mailTemplateContent = MailTemplateContent::query()
            ->where('mail_template_id', '=', $mailTemplate->id)
            ->where('locale', $locale)->first();

        $templateContent = $mailTemplateContent->content;

        $mailTemplateVariables = config('mail-templates.global_variables');
        
        $mailTemplateVariables += $notification->getTemplateVariables($notifiable);

        // Global variables assignment
        $mailTemplateVariables['username'] = isset($notifiable->username) ? $notifiable->username : '';
        $mailTemplateVariables['name'] = isset($notifiable->name) ? $notifiable->name : '';
        $mailTemplateVariables['email'] = isset($notifiable->email) ? $notifiable->email : '';

        foreach ($mailTemplateVariables as $mailTemplateVariable => $val) {
            $templateContent = str_replace('[' . $mailTemplateVariable . ']', $val, $templateContent);
        }

        //return $templateContent;
        return (new MailMessage)
            ->subject($mailTemplateContent->subject)
            ->view('vendor.notifications.email', compact('templateContent'));
    }
}