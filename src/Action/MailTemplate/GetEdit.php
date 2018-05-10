<?php

namespace Levaral\Core\Action\MailTemplate;

use App\Http\Actions\GetAction;
use Levaral\Core\Services\MailTemplateService;

class GetEdit extends GetAction
{
    /**
     * @var MailTemplateService
     */
    private $mailTemplateService;

    public function __construct(MailTemplateService $mailTemplateService)
    {
        $this->mailTemplateService = $mailTemplateService;
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
        return view(
            'vendor.core.mail-template.form',
            $this->mailTemplateService->getTemplateEdit($templateId, request('locale', 'en'))
        );
    }
}