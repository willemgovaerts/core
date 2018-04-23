<?php

namespace Levaral\Core\Action\MailLog;

use App\Domain\MailLog\MailLog;

class MailWebHookService
{
    public function store(array $input)
    {
        $mailLogs = MailLog::find(array_get($input, 'model_id'));

        if (!$mailLogs) {
            return;
        }

        $event = array_get($input, 'event');
        $reason = array_get($input, 'reason') . '-' . array_get($input, 'code');
        $now = \Carbon\Carbon::now();

        if ($event == 'delivered') {
            $mailLogs->mail_sent_at = $now;
        } else if ($event == 'failed') {
            $mailLogs->error_reason =  $reason;
            $mailLogs->mail_fail_at = $now;
        } else if ($event == 'opened') {
            $mailLogs->mail_opened_at = $now;
        } else if ($event == 'clicked') {
            $mailLogs->mail_clicked_at = $now;
        } else if ($event == 'unsubscribed') {
            $mailLogs->mail_unsubscribed_at = $now;
        } else if ($event == 'complained') {
            $mailLogs->mail_complained_at = $now;
        } else if ($event == 'stored') {
            $mailLogs->mail_stored_at = $now;
        } else if ($event == 'dropped') {
            $mailLogs->error_reason = $reason;
            $mailLogs->mail_fail_at = $now;
        } else if ($event == 'bounced') {
            $mailLogs->error_reason = $reason;
            $mailLogs->mail_fail_at = $now;
        }

        $mailLogs->save();

        return $mailLogs;
    }
}