@extends('admin.app')

@section('content')
    <link href="{{ URL::asset('vendors/css/codemirror.min.css') }}" rel="stylesheet">
    <div class="card">
        <div class="card-header">
            <strong  id="notification-title">Add Email Notification</strong>
        </div>
        <div class="card-body">
            @if($errors)
                @foreach($errors as $error)
                    <div class="alert alert-danger" role="alert">
                        <p class="m-0">{{ $error }}</p>
                    </div>
                @endforeach
            @endif
            <form method="post" action="{{route('notifications.store')}}" class="form-group" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Source</label>
                            <select class="form-control" name="source" required="required">
                                <option value="" selected disabled>Select Source </option>
                                @foreach($sources as $source)
                                    <option value="{{ $source }}" @if(old('source') == $source || (!empty($data['source']) && $data['source'] == $source)) selected @endif>{{ \Illuminate\Support\Str::title($source) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Event</label>
                            <select class="form-control" name="event" required="required">
                                <option value="" selected disabled>Select Event </option>
                                @foreach($events as $event)
                                    <option value="{{ $event }}" @if(old('event') == $event || (!empty($data['identifier']) && $data['identifier'] == $event)) selected @endif>{{ \Illuminate\Support\Str::title(str_replace("_", " ", $event)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="channel" required="required">
                                <option value="" selected disabled>Select Type </option>
                                @foreach($channels as $channel)
                                    <option value="{{ $channel }}" @if(old('event') == $channel || (!empty($data['channel']) && $data['channel'] == $channel)) selected @endif>{{ \Illuminate\Support\Str::title($channel) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>Page ID</label>
                        <div class="input-group">
                            <input autocomplete="off" id="page_id" name="page_id" maxlength="50" type="text" class="form-control" @if(!empty($data['data']['page_id'])) value="{{ $data['data']['page_id'] }}" @endif placeholder="Enter Page ID">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>To Name</label>
                        <div class="input-group">
                            <input id="to_name" autocomplete="off" name="to_name" maxlength="50" type="text" class="form-control" @if(!empty($data['data']['to_name'])) value="{{ $data['data']['to_name'] }}" @endif>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>To Email</label>
                        <div class="input-group">
                            <input id="to_email" autocomplete="off" name="to_email" maxlength="50" type="text" class="form-control" @if(!empty($data['data']['to_email'])) value="{{ $data['data']['to_email'] }}" @endif>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>Subject</label>
                        <div class="input-group">
                            <input id="subject" autocomplete="off" name="subject" maxlength="50" type="text" class="form-control" @if(!empty($data['data']['subject'])) value="{{ $data['data']['subject'] }}" @endif>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <label><i class="fa fa-file" aria-hidden="true"></i> Upload file</label>
                        <input type="file" name="file" accept=".pdf" class="form-control">
                    </div>
                    <div class="col-sm-3">
                        <label for="cutoff_date">Cut Off Date</label>
                        <div class="input-group">
                            <input type="datetime-local" id="cutoff_date" name="cutoff_date" class="form-control"@if(!empty($data['data']['cutoff_datetime'])) value="{{ $data['data']['cutoff_datetime'] }}" @endif></div>
                    </div>
                    <div class="col-sm-2">
                        <label>Test Mode?</label>
                        <div class="input-group">
                            <label class="switch switch-icon switch-pill switch-success">
                                <input type="checkbox" class="switch-input" name="test" id="test" @if(request('test') == 'on') checked @endif>
                                <span class="switch-label" data-on="" data-off=""></span>
                                <span class="switch-handle"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>Active?</label>
                        <div class="input-group">
                            <label class="switch switch-icon switch-pill switch-success">
                                <input type="checkbox" class="switch-input" name="active" id="active" @if(request('active') == 'on') checked @endif>
                                <span class="switch-label" data-on="" data-off=""></span>
                                <span class="switch-handle"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">Email Template</div>
                            <textarea name="email_template" id="codemirror"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                            <button id="update-notification-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-send-o"></i> Update</button>
                            <button class="btn btn-group-sm btn-danger pull-right" type="reset"><i class="fa fa-remove"></i> Clear</button>
                    </div>
                </div>
                {{ csrf_field() }}
            </form>
        </div>
    </div>
    @if(isset($notification) && $notification == 'create')
        <div class="alert alert-success" role="alert">
            <p class="m-0"><strong style="color: green">Notification created in the backend.</strong></p>
        </div>
    @elseif(isset($notification) && $notification == 'update')
        <div class="alert alert-success" role="alert">
            <p class="m-0"><strong style="color: green">Notification updated in the backend.</strong></p>
        </div>
    @endif

@endsection
@section('footer-js')
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/codemirror.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/code-editor.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
@endsection
