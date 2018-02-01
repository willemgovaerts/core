@foreach($namespaces as $namespace => $methods)
@include('core::stubs.apiNamespaceClass', compact('namespace', 'methods'))

@endforeach

export default class Api {
    constructor(axios) {
        this.axios = axios;
@foreach($namespaces as $namespace => $_)
        this.{{lcfirst($namespace)}} = new {{$namespace}}Api(this.axios);
@endforeach
    }
}