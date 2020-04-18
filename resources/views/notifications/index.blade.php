@extends('admin.app')

@section('content')
    <link href="{{ URL::asset('vendors/css/codemirror.min.css') }}" rel="stylesheet">
    <div class="card">
        <div class="card-header">
            <strong  id="notification-title">Notification</strong>
            <a class="btn btn-group-sm btn-success pull-right" href="{{route('notifications.create')}}"><i class="fa fa-send-o"></i> Create</a>
        </div>
        <div class="card-body">
            @if($errors)
                @foreach($errors as $error)
                    <div class="alert alert-danger" role="alert">
                        <p class="m-0">{{ $error }}</p>
                    </div>
                @endforeach
            @endif
            @if(session('notification-message'))
                    <div class="alert alert-success" role="alert">
                        <p class="m-0">{{ session('notification-message')  }}</p>
                    </div>
            @endif

            @if(!empty($data))
                <table class="table table-striped table-bordered datatable">
                    <thead>
                    <tr>
                        <th>Source</th>
                        <th>Event</th>
                        <th>Page ID</th>
                        <th>To Name</th>
                        <th>To Mail</th>
                        <th>Cut off Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $document)
                        <tr>
                            <td>{{$document['source']}}</td>
                            <td>{{$document['identifier']}}</td>
                            <td>{{$document['data']['page_id'] ?? ''}}</td>
                            <td>{{$document['data']['to_name'] ?? ''}}</td>
                            <td>{{$document['data']['to_email'] ?? ''}}</td>
                            <td>{{date(\App\Models\WebhookNotification::CUTOFF_DATE_FORMAT,$document['data']['cutoff_datetime']) ?? 0 }}
                                @if($document['data']['cutoff_datetime'] < time())
                                    <span class="badge badge-warning">Deactivated</span>
                                @endif
                            </td>
                            <td>@if(isset($document['data']['active']) && $document['data']['active'] == 1)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">InActive</span>
                                @endif
                                @if(isset($document['data']['test_mode']) && $document['data']['test_mode'] == 1)
                                    <span class="badge badge-warning">Sandbox</span>
                                @endif
                                <p class="pull-right">
                                    <a href="{{route('notifications.edit',['id'=>$document['_id']])}}" type="button" class="fa fa-edit"></a>
                                </p></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="row pull-right mr-4">
                    {!! $data->appends(request()->query())->render() !!}
                </div>
            @else
                <h2 class="text-danger">No Data Found</h2>
            @endif
        </div>
    </div>
@endsection
@section('footer-js')
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/codemirror.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/code-editor.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
@endsection
