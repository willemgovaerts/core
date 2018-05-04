@extends('layouts.app')
@section('content')
    <div class="card">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">Id</th>
                <th scope="col">Type</th>
                <th scope="col">Action</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($mailTemplates as $key => $mailTemplate)
                <tr>
                    <th scope="row">{{ $key+1 }}</th>
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
    </div>
@endsection