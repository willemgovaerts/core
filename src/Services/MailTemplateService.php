<?php

namespace Levaral\Core\Services;

use Illuminate\Support\Facades\File;
use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;
use Levaral\Core\DTO\MailTemplateContentDTO;
use Levaral\Core\Util;

class MailTemplateService
{
    public function createMailTemplateByLocals()
    {
        $notificationFiles = $this->getNotificationFiles();

        // Remove all the template entries which are not in the notification files list
        MailTemplate::query()->whereNotIn('type', $notificationFiles)->forceDelete();

        foreach ($notificationFiles as $notificationFile) {
            $this->createMailTemplates($notificationFile);
        }
    }

    public function createMailTemplates($notificationFile, $locale = null)
    {
        // TODO: what to do with template content if template is created new by deleting and existing one?
        $notificationClass = 'App\\Notifications\\' . $notificationFile;
        // Get the locale directories
        $locales = $this->getLocaleDirectories();

        if (!class_exists($notificationClass)) {
            return false;
        }

        //check if getTemplateVariables method
        if (!isset($notificationClass::$templateVariables)) {
            return false;
        }

        //if template already exist then update
        $mailTemplate = MailTemplate::query()->firstOrNew(['type' => $notificationFile]);
        $mailTemplate->save();

        foreach ($locales as $locale) {
            //if template already exist then don't do anything
            $mailTemplateContent = MailTemplateContent::query()
                ->where('locale', $locale)
                ->where('mail_template_id', $mailTemplate->getId())
                ->first();

            if ($mailTemplateContent) {
                continue;
            }

            $mailTemplateContent = new MailTemplateContent();
            $mailTemplateContent->setLocale($locale);
            $mailTemplateContent->setMailTemplateId($mailTemplate->getId());
            $mailTemplateContent->save();
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

        $mailTemplateContent = Util::setDTOData($mailTemplateContent, $mailTemplateContentDTO);
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
            $notificationFiles[] = rtrim($file->getFileName(), '.'. $file->getExtension());
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

        return $message->viewData;
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

        $templateVariables = (isset($notificationClass::$templateVariables)) ? array_keys($notificationClass::$templateVariables) : [];

        $templateVariables = array_merge($templateVariables, array_keys(config('mail-templates.global_variables', [])));

        foreach (config('mail-templates.button_style') as $type => $style) {
            $templateVariables[] = $type.'Button'.':'.'any link tag'.':'.'Button Text';
        }

        //wrap all tags with square bracate
        $templateVariables = array_map(function($variable){
            return '['.$variable.']';
        }, $templateVariables);

        return compact(
            'mailTemplate',
            'templateVariables',
            'locales',
            'locale',
            'templateContent'
        );
    }
}