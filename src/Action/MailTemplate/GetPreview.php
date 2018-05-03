<?php
namespace Levaral\Core\Action\MailTemplate;

use App\Domain\MailTemplate\MailTemplate;
use App\Http\Actions\GetAction;

class GetPreview extends GetAction
{
    public function __construct()
    {
        //
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
        $templateContent = $message->viewData;
        $templateContent = $templateContent['templateContent'];
        return view('vendor.notifications.email', compact('templateContent'));
    }
}