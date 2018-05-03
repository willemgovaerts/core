@extends('layouts.app')
@section('content')
<table class="table table-responsive">
    <thead>
        <tr>
            <th>Id</th>
            <th>Type</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    @foreach($mailTemplates as $mailTemplate)
        <tr>
            <td>{{ $mailTemplate->getId() }}</td>
            <td>{{ $mailTemplate->getType() }}</td>
            <td>
                <a href="{{ route('MailTemplate.GetEdit', ['id'=>$mailTemplate->getId()]) }}" class="btn btn-default">Edit</a>
                <a href="{{ route('MailTemplate.GetPreview', ['id'=>$mailTemplate->getId()]) }}" class="btn btn-default">Preview</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endsection