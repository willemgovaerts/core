{!! $phpTag !!}

namespace App\Domain\MailTemplate;

use App\Domain\Base\MailTemplate\BaseMailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;

class MailTemplate extends BaseMailTemplate
{
    protected $fillable = [
        'type'
    ];
}
