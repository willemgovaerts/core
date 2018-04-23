<?php

namespace Levaral\Core\Channels;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MailChannel extends \Illuminate\Notifications\Channels\MailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->routeNotificationFor('mail')) {
            return;
        }

        $message = $notification->toMail($notifiable);

        if (! $notifiable->routeNotificationFor('mail', $notification) &&
            ! $message instanceof Mailable) {
            return;
        }

        if ($message instanceof Mailable) {
            return $message->send($this->mailer);
        }

        $this->mailer->send(
            $this->buildView($message),
            $message->data(),
            $this->messageBuilder($notifiable, $notification, $message)
        );
    }

    /**
     * Get the mailer Closure for the message.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @param  \Illuminate\Notifications\Messages\MailMessage  $message
     * @return \Closure
     */
    protected function messageBuilder($notifiable, $notification, $message)
    {
        return function ($mailMessage) use ($notifiable, $notification, $message) {
            $this->buildMessage($mailMessage, $notifiable, $notification, $message);
        };
    }

    /**
     * Build the mail message.
     *
     * @param  \Illuminate\Mail\Message  $mailMessage
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @param  \Illuminate\Notifications\Messages\MailMessage  $message
     * @return void
     */
    protected function buildMessage($mailMessage, $notifiable, $notification, $message)
    {
        $this->addressMessage($mailMessage, $notifiable, $notification, $message);

        $mailMessage->subject($message->subject ?: Str::title(
            Str::snake(class_basename($notification), ' ')
        ));

        $this->addAttachments($mailMessage, $message);

        if (! is_null($message->priority)) {
            $mailMessage->setPriority($message->priority);
        }

        if (isset($notifiable->model_id)) {
            $headers = $mailMessage->getHeaders();
            $headers->addTextHeader('X-Mailgun-Variables', json_encode(['model_id' => $notifiable->model_id]));
            $headers->addTextHeader('X-SMTPAPI', json_encode([
                'unique_args' => [
                    'model_id' => $notifiable->model_id
                ]
            ]));
        }
    }
}


