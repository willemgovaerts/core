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

    CONST MAIL_SENT = [self::DELIVERED, self::PROCESSED];
    CONST MAIL_OPEND = [self::OPENED, self::OPEN];
    CONST MAIL_CLICKED = [self::CLICKED, self::CLICK];
    CONST MAIL_UNSUBSCRIBED = [self::UNSUBSCRIBED, self::UNSUBSCRIBE];
    CONST MAIL_COMPLAINED = [self::COMPLAINED];
    CONST MAIL_STORED = [self::STORED];
    CONST MAIL_FAIL = [self::DROPPED,  self::BOUNCED, self::BOUNC, self::FAILED];


    public function create(MailLogDTO $mailLogDTO)
    {
        $mailLogs = MailLog::find($mailLogDTO->model_id);

        if (!$mailLogs) {
            return;
        }

        $reason = $mailLogDTO->reason . '-' . $mailLogDTO->code;

        $now = \Carbon\Carbon::now();

        if (in_array($mailLogDTO->event, self::MAIL_SENT)) {
            $mailLogs->mail_sent_at = $now;
        } elseif (in_array($mailLogDTO->event, self::MAIL_OPEND)) {
            $mailLogs->mail_opened_at = $now;
        } elseif (in_array($mailLogDTO->event, self::MAIL_CLICKED)) {
            $mailLogs->mail_clicked_at = $now;
        } elseif (in_array($mailLogDTO->event, self::MAIL_UNSUBSCRIBED)) {
            $mailLogs->mail_unsubscribed_at = $now;
        } elseif (in_array($mailLogDTO->event, self::MAIL_COMPLAINED)) {
            $mailLogs->mail_complained_at = $now;
        } elseif (in_array($mailLogDTO->event, self::MAIL_STORED)) {
            $mailLogs->mail_stored_at = $now;
        } elseif (in_array($mailLogDTO->event, self::MAIL_FAIL)) {
            $mailLogs->error_reason = $reason;
            $mailLogs->mail_fail_at = $now;
        }

        $mailLogs->save();

        return $mailLogs;
    }
}