<?php

namespace Levaral\Core\Action\MailLog;

use App\Http\Actions\GetAction;
use Levaral\Core\Action\MailLog\MailWebHookService;

class MailGunHook extends GetAction
{
    protected $mailWebHookService;

    public function __construct(MailWebHookService $mailWebHookService)
    {
        $this->mailWebHookService = $mailWebHookService;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function execute()
    {
        if (!request()->get('model_id')) {
            return;
        }

        return $this->mailWebHookService->store(request()->all());
    }
}