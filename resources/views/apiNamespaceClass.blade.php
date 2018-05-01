class {{ $namespace }}Api {
    constructor(axios) {
        this.axios = axios;
    }
@foreach($methods as $method)
    {{lcfirst($method['name'])}}({{ implode(', ', $method['params']) }}) {
    @if($method['method'] == 'post')
    return this.axios.{{ $method['method'] }}(`{{  '/' . ltrim($method['path']) }}`, formData, callback);
    @else
    return this.axios.{{ $method['method'] }}(`{{  '/' . ltrim($method['path']) }}`, {params: formData});
    @endif
}
@endforeach
}