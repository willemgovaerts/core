<?php

namespace Levaral\Core\Action\MailLog;

use App\Http\Actions\PostAction;
use Levaral\Core\Action\Services\MailWebHookService;
use Levaral\Core\DTO\MailLogDTO;

class PostMailGunHook extends PostAction
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
        $input = [
            'model_id' => (int) request()->get('model_id', 0),
            'event' => request()->get('event'),
            'reason' => request()->get('reason'),
            'code' => request()->get('code')
        ];

        $mailLogDTO = new MailLogDTO($input);

        if (!$mailLogDTO->model_id) {
            return;
        }

        return $this->mailWebHookService->create($mailLogDTO);
    }
}