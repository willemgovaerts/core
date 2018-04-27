<?php
namespace Levaral\Core\Action\MailTemplate;

use App\Domain\MailTemplate\MailTemplate;
use App\Http\Actions\GetAction;

class GetPreview extends GetAction
{
    public function __construct()
    {

    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function execute($templateId)
    {
        $mailTemplate = MailTemplate::query()->find($templateId);
        $notificationClass = 'App\\Notifications\\' . $mailTemplate->getType();
        $notification = $notificationClass::preview();
        $message = $notification->toMail($this->user());
        $mailTemplate = $this->getTemplate($mailTemplate, $notification);
        return view('vendor.notifications.email', compact('mailTemplate'));
    }

    public function getTemplate($mailTemplate, $notification)
    {
        $mailContent = $mailTemplate->content->where('locale_code', 'en')->first();
        $mailTemplateVariable = $notification->getTemplateVariables($notification);
        $output = str_replace(array_keys($mailTemplateVariable), array_values($mailTemplateVariable), $mailContent->content);
        $output = str_replace(['[', ']'], ' ' , $output);
        $mailTemplate->content->content = $mailContent->content = $output;
        return $mailTemplate;
    }
}