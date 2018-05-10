@extends('layouts.app')
@section('content')
    <div class="card">
        <table class="table">
            <thead>
            <tr>
                <th>Id</th>
                <th>Type</th>
                <th width="15%">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($mailTemplates as $key => $mailTemplate)
                <tr>
                    <th scope="row">{{ $key+1 }}</th>
                    <td>{{ $mailTemplate->getType() }}</td>
                    <td>
                        <a href="{{ route('MailTemplate.GetEdit', ['id'=>$mailTemplate->getId()]) }}" class="btn btn-secondary">Edit</a>
                        <a href="{{ route('MailTemplate.GetPreview', ['id'=>$mailTemplate->getId()]) }}" class="btn btn-secondary">Preview</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection