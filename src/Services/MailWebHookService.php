<?php

namespace Levaral\Core\Action\Services;

use App\Domain\MailLog\MailLog;
use Levaral\Core\DTO\MailLogDTO;

class MailWebHookService
{
    CONST UNSUBSCRIBED = 'unsubscribed';
    CONST UNSUBSCRIBE = 'unsubscribe';
    CONST DELIVERED = 'delivered';
    CONST PROCESSED = 'processed';
    CONST OPENED = 'opened';
    CONST OPEN = 'open';
    CONST FAILED = 'failed';
    CONST COMPLAINED = 'complained';
    CONST CLICKED = 'clicked';
    CONST CLICK = 'click';
    CONST DROPPED = 'dropped';
    CONST BOUNCED = 'bounced';
    CONST BOUNC = 'bounc';
    CONST STORED = 'stored';

    public function create(MailLogDTO $mailLogDTO)
    {
        $mailLogs = MailLog::find($mailLogDTO->model_id);

        if (!$mailLogs) {
            return;
        }
        $reason = $mailLogDTO->reason . '-' . $mailLogDTO->code;

        $now = \Carbon\Carbon::now();

        if ($mailLogDTO->event == self::DELIVERED || $mailLogDTO->event == self::PROCESSED) {
            $mailLogs->mail_sent_at = $now;
        } else if ($mailLogDTO->event == self::OPENED || $mailLogDTO->event == self::OPEN) {
            $mailLogs->mail_opened_at = $now;
        } else if ($mailLogDTO->event == self::CLICKED || $mailLogDTO->event == self::CLICK) {
            $mailLogs->mail_clicked_at = $now;
        } else if ($mailLogDTO->event == self::UNSUBSCRIBED || $mailLogDTO->event == self::UNSUBSCRIBE) {
            $mailLogs->mail_unsubscribed_at = $now;
        } else if ($mailLogDTO->event == self::COMPLAINED) {
            $mailLogs->mail_complained_at = $now;
        } else if ($mailLogDTO->event == self::STORED) {
            $mailLogs->mail_stored_at = $now;
        } else if ($mailLogDTO->event == self::DROPPED
            || $mailLogDTO->event == self::BOUNCED
            || $mailLogDTO->event == self::BOUNC
            || $mailLogDTO->event == self::FAILED) {
            $mailLogs->error_reason = $reason;
            $mailLogs->mail_fail_at = $now;
        }

        $mailLogs->save();

        return $mailLogs;
    }
}