<?php
/**
 * Created by PhpStorm.
 * User: levaral
 * Date: 02/05/18
 * Time: 7:22 PM
 */

namespace Levaral\Core\Action\MailTemplate;

use App\Domain\User\User;
use App\Domain\MailTemplate\MailTemplate;
use App\Http\Actions\GetAction;
use Illuminate\Support\Facades\Mail;
use Levaral\Core\Util;

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

        $notification = new $notificationClass();
        $user = User::query()->find(5);
        Util::notify($user, $notification);
    }
}