<?php
/**
 * Created by PhpStorm.
 * User: levaral
 * Date: 24/04/18
 * Time: 6:51 PM
 */

namespace Levaral\Core\Action\MailTemplate;

use App\Http\Actions\PostAction;
use App\Domain\MailTemplate\MailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;
use Levaral\Core\DTO\MailTemplateContentDTO;
use Levaral\Core\Services\MailTemplateService;

class PostSave extends PostAction
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
        return [
            'subject'=>'required|string',
            'content'=>'required|string'
        ];
    }

    public function execute($templateId = null, $locale = 'en')
    {
        $mailTemplateContentDTO = new MailTemplateContentDTO($this->data());
        $mailTemplateContentDTO->mail_template_id = $templateId;
        $mailTemplateContentDTO->locale = $locale;

        $this->mailTemplateService->createMailTemplateContent($mailTemplateContentDTO);

        return redirect()->route('MailTemplate.GetList');
    }
}