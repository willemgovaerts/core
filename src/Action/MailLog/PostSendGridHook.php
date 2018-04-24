<?php

namespace Levaral\Core\Action\MailLog;

use App\Http\Actions\PostAction;
use Levaral\Core\Action\Services\MailWebHookService;
use Levaral\Core\DTO\MailLogDTO;

class PostSendGridHook extends PostAction
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
        $requestData = isset($requestData[1]) ? $requestData[1] : $requestData[0];

        $input = [
            'model_id' => (int) array_get($requestData, 'model_id', 0),
            'event' => array_get($requestData, 'event'),
            'reason' => array_get($requestData, 'reason'),
            'code' => array_get($requestData, 'code')
        ];

        $mailLogDTO = new MailLogDTO($input);
        
        if (!$mailLogDTO->model_id) {
            return;
        }

        return $this->mailWebHookService->create($mailLogDTO);
    }
}