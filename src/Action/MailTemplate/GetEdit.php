<?php

namespace Levaral\Core\Action\MailTemplate;

use App\Http\Actions\GetAction;
use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;

class GetEdit extends GetAction
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

        // Check for the notification class file, if doesn't exists then redirect to list page.
        if (!class_exists($notificationClass)) {
            return redirect()->route('MailTemplate.GetList');
        }

        $notification = new $notificationClass();

        $templateVariables = $notification->templateVariables;

        return view('vendor.core.MailTemplate.form', compact('mailTemplate', 'templateVariables'));
    }
}