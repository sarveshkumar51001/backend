@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            @if(\Illuminate\Support\Facades\Route::current()->getName() != 'notifications.create')
                <strong  id="notification-title">Edit Email Notification</strong>
            @else
                <strong  id="notification-title">Add Email Notification</strong>
            @endif
        </div>
        <div class="card-body">
            @if($errors)
                @foreach($errors->all() as $error)
                    <div class="alert alert-danger" role="alert">
                        <p class="m-0">{{ $error }}</p>
                    </div>
                @endforeach
            @endif

                @if(\Illuminate\Support\Facades\Route::current()->getName() == 'notifications.edit')
                    <form method="post" action="{{route('notifications.update',$data['_id'])}}" class="form-group" enctype="multipart/form-data" id="notification-form">
                    <input type="hidden" name="_method" value="PUT">
                @else
                    <form method="post" action="{{route('notifications.store')}}" class="form-group" enctype="multipart/form-data" id="notification-form">
                @endif
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Source</label>
                            <select class="form-control" name="source" required="required">
                                <option value="" selected disabled>Select Source </option>
                                @foreach($sources as $source)
                                    <option value="{{ $source }}" @if( old('source', $data['source'] ?? '') == $source) selected @endif>{{ \Illuminate\Support\Str::title($source) }}</option>
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
                                    <option value="{{ $event }}" @if(old('event', $data['identifier'] ?? '') == $event) selected @endif>{{ \Illuminate\Support\Str::title(str_replace("_", " ", $event)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="type" required="required">
                                <option value="" selected disabled>Select Type </option>
                                @foreach($channels as $channel)
                                    <option value="{{ $channel }}" @if(old('type', $data['channel'] ?? '') == $channel) selected @endif>{{ \Illuminate\Support\Str::title($channel) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>Page ID</label>
                        <div class="input-group">
                            <input autocomplete="off" id="page_id" name="page_id" maxlength="50" type="text" class="form-control" value="{{old('page_id', $data['data']['page_id'] ?? '')}}" placeholder="Enter Page ID">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>To Name</label>
                        <div class="input-group">
                            <input id="to_name" autocomplete="off" name="to_name" maxlength="50" type="text" class="form-control" value="{{old('to_name', $data['data']['to_name'] ?? '')}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>To Email</label>
                        <div class="input-group">
                            <input id="to_email" autocomplete="off" name="to_email" maxlength="50" type="text" class="form-control" value="{{old('to_email', $data['data']['to_email'] ?? '')}}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>Subject</label>
                        <div class="input-group">
                            <input id="subject" autocomplete="off" name="subject" maxlength="50" type="text" class="form-control" value="{{old('subject', $data['data']['subject'] ?? '')}}">
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <label><i class="fa fa-file" aria-hidden="true"></i> Upload file</label>
                        <input type="file" name="file" accept=".pdf" class="form-control">
                    </div>
                    <div class="col-sm-3">
                        <label for="cutoff_date">Cut Off Date</label>
                        <div class="input-group">
                            <input id="cutoff_date" type='text' name="cutoff_date" class="form-control" value="{{ old('cutoff_date', date('d/m/Y h:i A',$data['data']['cutoff_datetime'] ?? timestamp())) }}">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>Test Mode?</label>
                        <div class="input-group">
                            <label class="switch switch-icon switch-pill switch-success">
                                <input type="checkbox" class="switch-input" name="test" id="test" @if(old('test') == 'on' || (!empty($data['data']['test_mode']) && $data['data']['test_mode'] == 1)) checked @endif>
                                <span class="switch-label" data-on="" data-off=""></span>
                                <span class="switch-handle"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <label>Active?</label>
                        <div class="input-group">
                            <label class="switch switch-icon switch-pill switch-success">
                                <input type="checkbox" class="switch-input" name="active" id="active" @if(old('active') == 'on' || (!empty($data['data']['active']) && $data['data']['active'] == 1)) checked @endif>
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
                            <textarea name="email_template" id="editor1">
                                {{old('email_template', $data['data']['template'] ?? '')}}
                            </textarea>
                        </div>
                    </div>
                </div>
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-send-o"></i> Save</button>
                    </div>
                </div>

            </form>
            </div>
        </div>
        @endsection
@section('footer-js')
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="https://cdn.ckeditor.com/4.9.2/standard/ckeditor.js"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
    <script>
        CKEDITOR.replace('editor1');

        $('#cutoff_date').daterangepicker({
            singleDatePicker:true,
            timePicker: true,
            locale: {
                format: 'DD/MM/YYYY hh:mm A'
            }
        });
    </script>
@endsection
