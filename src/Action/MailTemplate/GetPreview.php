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

        // TODO: do we really need to call this?
        $message = $notification->toMail($this->user());

        $templateContent = $message->viewData;
        $templateContent = (isset($templateContent['templateContent'])) ? $templateContent['templateContent'] : '';

        return view('vendor.notifications.email', compact('templateContent'));
    }
}