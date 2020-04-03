@extends('admin.app')

@section('content')
    <link href="{{ URL::asset('vendors/css/codemirror.min.css') }}" rel="stylesheet">
    @if(is_admin())
        <div class="card">
            <div class="card-header">
                <strong  id="notification-title">Add Notification</strong>
            </div>
        <div class="card-body">
            @if(!empty($errors))
                <div class="alert alert-danger" role="alert">
                    @foreach($errors as $error)
                        <p class="m-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <form method="post" action="{{route('notification.create')}}" class="form-group" enctype="multipart/form-data">
                <div class="row ml-3 mr-4">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Source</label>
                            <select class="form-control" name="source" required="required">
                                <option value="" selected disabled>Select Source </option>
                                <option value="Instapage" @if("Instapage" == old('source')) selected="selected" @endif>Instapage</option>
                            </select>
                                </div>
                            </div>
                    <div class="col-sm-3">
                    <div class="form-group">
                        <label>Event</label>
                        <select class="form-control" name="event" required="required">
                            <option value="" selected disabled>Select Event </option>
                            <option value="Lead Create" @if("Lead Create" == old('event')) selected="selected" @endif>Lead Create</option>
                        </select>
                    </div>
                </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" name="type" required="required">
                                <option value="" selected disabled>Select Type </option>
                                <option value="SMS" @if("SMS" == old('type')) selected="selected" @endif>SMS</option>
                                <option value="Email" @if("Email" == old('type')) selected="selected" @endif>Email</option>
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
                    <label>Subject</label>
                    <div class="input-group">
                        <input id="subject" autocomplete="off" name="subject" maxlength="50" type="text" class="form-control" @if(!empty($data['data']['subject'])) value="{{ $data['data']['subject'] }}" @endif>
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
                        <label>Email Template</label>
                        <div class="input-group">
                            <textarea style="width:300px;height:200px" name="email_template" id="codemirror"></textarea>
                        </div>
                    </div>
                <div class="col-sm-3">
                    <label><i class="fa fa-file" aria-hidden="true"></i> Upload file</label>
                    <input type="file" name="file" required="required" accept=".pdf" class="form-control">
                </div>
                <div class="col-sm-3">
                    <label for="cutoff_date">Cut Off Date</label>
                <div class="input-group">
                    <input type="datetime-local" id="cutoff_date" name="cutoff_date" class="form-control"></div>
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
                <div class="col-sm-2 pull-left">
                    <label>&nbsp;</label>
                    <div class="input-group">
                        @if(!empty($data))
                            <button id="update-notification-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-send-o"></i> Update</button>
                            <a href={{route('notification.index')}}><button type="button" class="btn btn-group-sm btn-danger"> Clear</button></a>
                        @else
                            <button id="add-notification-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-send-o"></i> Create</button>
                            <a href={{route('notification.index')}}><button type="button" class="btn btn-group-sm btn-danger"> Clear</button></a>
                            @endif
                    </div>
                </div>
            </div>
                {{ csrf_field() }}
            </form>
        </div>
        </div>
        @endif
        @if(!empty($notification))
            <strong style="color: green">Notification created in the backend.</strong>
        @endif
        <table class="table table-striped table-bordered datatable">
            <thead>
            <tr>
                <th>Source</th>
                <th>Event</th>
                <th>Page ID</th>
                <th>To Name</th>
                <th>To Mail</th>
                <th>Cut off Date</th>
                <th>Active</th>
            </tr>
            </thead>
            <tbody>
            @if(!empty($documents))
            @foreach($documents as $document)
                <tr>
                    <td>{{$document['source']}}</td>
                    <td>{{$document['identifier']}}</td>
                    <td>{{isset($document['data']['page_id']) ? $document['data']['page_id'] : ''}}</td>
                    <td>{{isset($document['data']['to_name']) ? $document['data']['to_name']: ''}}</td>
                    <td>{{isset($document['data']['to_email']) ?$document['data']['to_email']:''}}</td>
                    <td>{{isset($document['data']['cutoff_datetime']) ? $document['data']['cutoff_datetime']:'' }}</td>
                    <td>{{isset($document['data']['active']) && $document['data']['active'] == 1 ? 'Yes':'No'}}
                    <p class="pull-right">
                        <a href="{{route('notification',['id'=>$document['_id']])}}" type="button" class="fa fa-edit"></a>
                    </p></td>
                </tr>
            @endforeach
                @endif
            </tbody>
                </table>
@endsection
@section('footer-js')
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/codemirror.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/code-editor.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
    @endsection
