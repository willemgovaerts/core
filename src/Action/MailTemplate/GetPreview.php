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
        return view('vendor.core.notifications.email', $this->mailTemplateService->getTemplateContent($templateId, $this->user()));
    }
}