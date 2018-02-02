{!! $phpTag !!}

namespace App\Http\Actions{{ ($path) ? '\\'.$path : '' }};


use App\Http\Actions\{{ $action }};

class {{ $class }} extends {{ $action }}
{
    public function __construct()
    {

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

    }
}