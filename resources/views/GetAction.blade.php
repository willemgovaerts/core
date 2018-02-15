{!! $phpTag !!}

namespace App\Http\Actions;

use Illuminate\Contracts\Validation\Validator;
use Levaral\Core\Action\BaseAction;

class GetAction extends BaseAction
{
    protected function failedValidation(Validator $validator)
    {

    }
}