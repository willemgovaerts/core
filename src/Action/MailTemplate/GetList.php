<?php

namespace Levaral\Core\Action\MailTemplate;

use App\Http\Actions\GetAction;
use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;
use Levaral\Core\Services\MailTemplateService;

class GetList extends GetAction
{
    protected $mailTemplateService;

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

    public function execute()
    {
        $mailTemplates = MailTemplate::query()->get();

        return view('vendor.core.mail-template.list', compact('mailTemplates'));
    }
}