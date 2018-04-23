{!! $phpTag !!}

namespace App\Domain\MailLog;

use App\Domain\Base\MailLog\BaseMailLog;

class MailLog extends BaseMailLog
{
    /**
    * Get all of the owning  models.
    */
    public function model()
    {
        return $this->morphTo();
    }
}