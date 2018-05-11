<?php

namespace Levaral\Core;


use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;
use Illuminate\Notifications\Notification;
use App\Domain\MailLog\MailLog;
use Illuminate\Notifications\Messages\MailMessage;

class Util
{
    public static function setDTOData($modelObject, $DTO, $only = [])
    {
        foreach ($DTO as $property => $value) {
            $property = camel_case($property);
            $method = 'set'.$property;

            if ($only && !in_array($property, $only)) {
                continue;
            }

            if(!$value || empty($value)) {
                continue;
            }

            if (!method_exists($modelObject, $method)) {
                continue;
            }

            $modelObject->{$method}($value);
        }

        return $modelObject;
    }

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
        $mailTemplateUrls = [];

        $mailTemplateContent = MailTemplateContent::query()
            ->where('mail_template_id', '=', $mailTemplate->id)
            ->where('locale', $locale)->first();

        $templateContent = $mailTemplateContent->content;

        $mailTemplateVariables = config('mail-templates.global_variables');
        
        $mailTemplateVariables = $notification->getTemplateVariables($notifiable) + $mailTemplateVariables;

        // Global variables assignment
        $mailTemplateVariables['username'] = isset($notifiable->username) ? $notifiable->username : '';
        $mailTemplateVariables['name'] = isset($notifiable->name) ? $notifiable->name : '';
        $mailTemplateVariables['email'] = isset($notifiable->email) ? $notifiable->email : '';

        foreach ($mailTemplateVariables as $mailTemplateVariable => $val) {
            $templateContent = str_replace('[' . $mailTemplateVariable . ']', $val, $templateContent);
        }

        foreach (config('mail-templates.button_style') as $type => $style) {
            preg_match("/\[".$type."Button:(.*):(.*)]/", $templateContent, $buttonData);
            if (!isset($buttonData[1]) || !isset($buttonData[2])) {
                continue;
            }

            $buttonUrl = isset($mailTemplateVariables[$buttonData[1]]) ? $mailTemplateVariables[$buttonData[1]] : '';
            $buttonText = $buttonData[2];
            $buttonStyle = $style;
            $buttonMarkup = view('vendor.core.notifications.partials.button', compact('buttonUrl', 'buttonText', 'buttonStyle'));
            $templateContent = preg_replace("/\[".$type."Button:(.*):(.*)]/", $buttonMarkup, $templateContent);
            $mailTemplateUrls[] = $buttonUrl;
        }

        //apply style for html tags
        foreach(config('mail-templates.tag_styles') as $tags => $style) {
            $templateContent = str_replace('<'.$tags.'>', '<'.$tags.' style="'.$style.'">', $templateContent);
        }

        //return $templateContent;
        return (new MailMessage)
            ->subject($mailTemplateContent->subject)
            ->view('vendor.core.notifications.email', compact('templateContent','mailTemplateUrls'));
    }
}