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


            @if(isset($notification) && $notification == 'create')
                <div class="alert alert-success" role="alert">
                    <p class="m-0"><strong style="color: green">Notification created in the backend.</strong></p>
                </div>
            @elseif(isset($notification) && $notification == 'update')
                <div class="alert alert-success" role="alert">
                    <p class="m-0"><strong style="color: green">Notification updated in the backend.</strong></p>
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
                        <th>Active</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $document)
                        <tr>
                            <td>{{$document['source']}}</td>
                            <td>{{$document['identifier']}}</td>
                            <td>{{isset($document['data']['page_id']) ? $document['data']['page_id'] : ''}}</td>
                            <td>{{isset($document['data']['to_name']) ? $document['data']['to_name']: ''}}</td>
                            <td>{{isset($document['data']['to_email']) ?$document['data']['to_email']:''}}</td>
                            <td>{{isset($document['data']['cutoff_datetime']) ? $document['data']['cutoff_datetime']:'' }}</td>
                            <td>{{isset($document['data']['active']) && $document['data']['active'] == 1 ? 'Yes':'No'}}
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
