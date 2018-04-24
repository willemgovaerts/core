<?php
namespace Levaral\Core\Action\MailTemplate;

use App\Http\Actions\GetAction;

class GetPreview extends GetAction
{
    public function __construct()
    {

    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function execute($className)
    {
        $class = 'App\\Notifications\\' . $className;
        $notification = $class::preview();
        $message = $notification->toMail($this->user());

        $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));

        return $markdown->render('vendor.notifications.email', $message->toArray());
    }
}