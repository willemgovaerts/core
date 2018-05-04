{!! $phpTag !!}

namespace App\Domain\MailTemplate;

use App\Domain\Base\MailTemplate\BaseMailTemplate;
use App\Domain\MailTemplate\MailTemplateContent;

class MailTemplate extends BaseMailTemplate
{
    protected $fillable = [
        'type'
    ];

    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function content()
    {
        return $this->hasMany('App\Domain\MailTemplate\MailTemplateContent');
    }
}
