<?php

namespace Levaral\Core\Services;

use Illuminate\Support\Facades\File;
use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;
use Levaral\Core\DTO\MailTemplateContentDTO;

class MailTemplateService
{
    public function createMailTemplateByLocals()
    {
        $notificationFiles = $this->getNotificationFiles();

        // TODO remove template content before deleting mail templates.

        // Remove all the template entries which are not in the notification files list
        MailTemplate::query()->whereNotIn('type', $notificationFiles)->delete();

        foreach ($notificationFiles as $notificationFile) {
            $this->createMailTemplates($notificationFile);
        }
    }

    public function createMailTemplates($notificationFile, $locale = null)
    {
        // TODO: what to do with template content if template is created new by deleting and existing one?
        $notificationClass = 'App\\Notifications\\' . $notificationFile;

        if (!class_exists($notificationClass)) {
            return false;
        }

        $notification = new $notificationClass();
        if (!empty($notification->templateVariables)) {
            $templateVariables = $notification->templateVariables;

            $mailTemplate = MailTemplate::query()->firstOrNew(['type' => $notificationFile]);
            $mailTemplate->variables = json_encode($templateVariables);
            $mailTemplate->save();

            // remove existing template content before creating new.
            $this->removeMailTemplateContent($mailTemplate->getId());

            // Get the locale directories
            $locales = $this->getLocaleDirectories();

            foreach ($locales as $locale) {
                $mailTemplateContentDTO = new MailTemplateContentDTO([]);
                $mailTemplateContentDTO->locale = $locale;
                $mailTemplateContentDTO->mail_template_id = $mailTemplate->getId();
                $this->createMailTemplateContent($mailTemplateContentDTO);
            }
        }
    }

    private function removeMailTemplateContent($templateId)
    {
        MailTemplateContent::query()->where('mail_template_id', $templateId)->delete();
    }

    public function createMailTemplateContent(MailTemplateContentDTO $mailTemplateContentDTO)
    {
        $mailTemplateContent = MailTemplateContent::query()
            ->where('mail_template_id', $mailTemplateContentDTO->mail_template_id)
            ->where('locale', $mailTemplateContentDTO->locale)
            ->first();

        if (!$mailTemplateContent) {
            $mailTemplateContent = new MailTemplateContent();
        }

        if (!empty($mailTemplateContentDTO->locale)) {
            $mailTemplateContent->setLocale($mailTemplateContentDTO->locale);
        }

        if (!empty($mailTemplateContentDTO->mail_template_id)) {
            $mailTemplateContent->setMailTemplateId($mailTemplateContentDTO->mail_template_id);
        }

        if (!empty($mailTemplateContentDTO->subject)) {
            $mailTemplateContent->setSubject($mailTemplateContentDTO->subject);
        }

        if (!empty($mailTemplateContentDTO->content)) {
            $mailTemplateContent->setContent($mailTemplateContentDTO->content);
        }

        $mailTemplateContent->save();

        return $mailTemplateContent;
    }

    private function getLocaleDirectories()
    {
        return array_diff(scandir(resource_path('lang')), array('..', '.'));
    }

    public function getNotificationFiles()
    {
        $notificationDirectory = app_path('Notifications');

        $notificationFiles = [];

        $files = File::allFiles($notificationDirectory);

        foreach ($files as $file) {
            $notificationFiles[] = str_replace(
                "." . pathinfo($file->getFileName(), PATHINFO_EXTENSION),
                '',
                $file->getFileName()
            );
        }

        return $notificationFiles;
    }

    public function getTemplateContent(int $templateId, $user)
    {
        $mailTemplate = MailTemplate::query()->find($templateId);
        $notificationClass = 'App\\Notifications\\' . $mailTemplate->getType();

        $notification = $notificationClass::preview();

        // TODO: do we really need to call this?
        $message = $notification->toMail($user);

        $templateContent = $message->viewData;
        return (isset($templateContent['templateContent'])) ? $templateContent['templateContent'] : '';
    }

    public function getTemplateEdit(int $templateId, string $locale)
    {
        $mailTemplate = MailTemplate::query()->find($templateId);

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

        $templateVariables = array_merge($templateVariables, config('mail-templates.global_variables', []));

        return compact(
            'mailTemplate',
            'templateVariables',
            'locales',
            'templateId',
            'locale',
            'templateContent'
        );
    }
}