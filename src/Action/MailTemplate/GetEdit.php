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

    public function execute($templateId, $locale = 'en')
    {
        $mailTemplate = MailTemplate::query()->with(['content'])->find($templateId);

        $templateContent = MailTemplateContent::query()
                            ->where('mail_template_id', $templateId)
                            ->where('locale', $locale)->first();

        $notificationClass = 'App\\Notifications\\' . $mailTemplate->getType();

        // Check for the notification class file, if doesn't exists then redirect to list page.
        if (!class_exists($notificationClass)) {
            return redirect()->route('MailTemplate.GetList');
        }

        // Get the locale directories
        $locales = array_diff(scandir(resource_path('lang')), array('..', '.'));

        $notification = new $notificationClass();

        $templateVariables = (isset($notification->templateVariables)) ? array_keys($notification->templateVariables) : [];

        $templateVariables = array_merge($templateVariables, config('mailtemplates.global_variables', []));

        return view(
            'vendor.core.MailTemplate.form',
            compact(
                'mailTemplate',
                'templateVariables',
                'locales',
                'templateId',
                'locale',
                'templateContent'
            )
        );
    }
}