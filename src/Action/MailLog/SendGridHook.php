<?php

namespace Levaral\Core\Action\MailLog;

use App\Http\Actions\GetAction;
use Levaral\Core\Action\Services\MailWebHookService;
use Levaral\Core\DTO\MailLogDTO;

class SendGridHook extends GetAction
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
        $requestData = request()->all();

        $input = [
            'model_id' => (int) array_get($requestData[1], 'model_id', 0),
            'event' => array_get($requestData[1], 'event'),
            'reason' => array_get($requestData[1], 'reason'),
            'code' => array_get($requestData[1], 'code')
        ];

        $mailLogDTO = new MailLogDTO($input);
        
        if (!$mailLogDTO->model_id) {
            return;
        }

        return $this->mailWebHookService->create($mailLogDTO);
    }
}