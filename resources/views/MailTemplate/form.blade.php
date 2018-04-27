<div class="row">
    <div class="col-md-12">
        @foreach($locales as $locale)
            <a href="{{ route('MailTemplate.GetEdit', ['id'=>$templateId, 'locale'=>$locale]) }}" class="btn btn-primary">{{ $locale }}</a>
        @endforeach
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <form method="post" class="horizontal-form" action="{{ route('MailTemplate.PostSave', ['id'=>$templateId, 'locale'=>$locale_code]) }}">
            <input type="hidden" name="locale" value="{{ $locale_code }}" />
            {{ csrf_field() }}
            <div class="row form-group">
                <label class="control-label col-md-3">Type</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="type" value="" />
                </div>
            </div>
            @foreach($errors as $error)
                <p>{{ $error }}</p>
            @endforeach
            <div class="row form-group">
                <label class="control-label col-md-3">Subject</label>
                <div class="col-md-9">
                    <input type="text" class="form-control" name="subject" value="" />
                    {{ $errors->first('subject') }}
                </div>
            </div>
            <div class="row form-group">
                <label class="control-label col-md-3">Content</label>
                <div class="col-md-9">
                    <textarea rows="4" col="80" name="content" class="form-control"></textarea>
                    {{ $errors->first('content') }}
                </div>
            </div>
            <div class="row form-group">
                <label class="control-label col-md-3">Variables</label>
                <div class="col-md-9">
                    @foreach($templateVariables as $templateVariable)
                        <span data-tag="[{{ $templateVariable }}]">{{ $templateVariable }}</span>
                    @endforeach
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 text-center">
                    <input type="submit" value="save" class="btn btn-success" />
                    <a href="{{ route('MailTemplate.GetList') }}" class="btn btn-default">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>