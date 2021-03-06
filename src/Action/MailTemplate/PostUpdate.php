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

class PostUpdate extends PostAction
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
            'locale' => 'required|string',
            'subject'=>'required|string',
            'content'=>'required|string'
        ];
    }

    public function execute($templateId = null)
    {
        $mailTemplateContentDTO = new MailTemplateContentDTO($this->data());
        $mailTemplateContentDTO->mail_template_id = $templateId;

        $this->mailTemplateService->createMailTemplateContent($mailTemplateContentDTO);

        return redirect()->back();
    }
}