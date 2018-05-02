<?php
/**
 * Created by PhpStorm.
 * User: levaral
 * Date: 02/05/18
 * Time: 7:22 PM
 */

namespace Levaral\Core\Action\MailTemplate;

use App\Domain\MailTemplate\MailTemplate;
use App\Http\Actions\GetAction;
use Illuminate\Support\Facades\Mail;

class GetSend extends GetAction
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
        $message = $notification->toMail($this->user());
        $templateContent = $message->viewData;
        $templateContent = $templateContent['templateContent'];
        $messageContent = view('vendor.notifications.email', compact('templateContent'));
        Mail::to($this->user())->send($messageContent);
//        return view('vendor.notifications.email', compact('templateContent'));
    }
}