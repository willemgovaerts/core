<?php
namespace Levaral\Core\Action\MailTemplate;

use App\Http\Actions\GetAction;
use Levaral\Core\Services\MailTemplateService;

class GetPreview extends GetAction
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
        $templateContent = $this->mailTemplateService->getTemplateContent($templateId, $this->user());
        return view('vendor.notifications.email', compact('templateContent'));
    }
}