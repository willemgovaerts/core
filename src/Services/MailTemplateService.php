<?php

namespace Levaral\Core\Services;

use Illuminate\Support\Facades\File;
use App\Domain\MailTemplate\MailTemplate;

class MailTemplateService
{
    public function createMailTemplateByLocals()
    {
        $locales = ['en', 'fr_fr'];

        foreach($locales as $locale){
            $this->createMailTemplates($locales);
        }
    }

    public function createMailTemplates($locale = null)
    {
        $notificationFiles = $this->getNotificationFiles();

        $mailTemplate = MailTemplate::query()->whereNotIn('type', $notificationFiles)->delete();

//        foreach($notificationFiles as $file) {
//            if (!$mailTemplate) {
//                $mailTemplate = new MailTemplate();
//            }
//
//            $mailTemplate->setType($fileName);
//
//            // TODO: get locale id from locale table
//            $mailTemplate->setLocaleId(1);
//
//            $mailTemplate->save();
//        }

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
}