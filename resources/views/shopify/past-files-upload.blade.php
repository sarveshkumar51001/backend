@extends('admin.app')
@section('content')
    <div class = "body">
        <div class="card">
            <div class="card-header">
                Following are the excel files uploaded by you in the past.
                <div class="row pull-right m-2">
                    <a href="{{ route('bulkupload.upload') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-plus"> &nbsp;</i>New Upload</button></a>
                    <a href="{{ route('bulkupload.previous_orders') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i>Previous Orders</button></a>
                </div>
            </div>
            <div class="card-body">

                <table class="table table-bordered table-striped table-sm datatable">
                    <thead>
                    <tr>
                        <th>Upload Date</th>
                        <th>Uploaded File</th>
                        <th>File ID</th>
                        <th>New order</th>
                        <th>Update order</th>
                        <th>Total Cash collected</th>
                        <th>Total Cheque collected</th>
                        <th>Total Online collected</th>
                        <th>Grand total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $files as $data)

                        <tr>
                            <td>{{ date('l jS F Y h:i:s A', $data->created_at) }}</td>
                            <td><a target="_blank" href="/bulkupload/previous/file_download/{{$data->id}}">{{ $data->file_name }}</a></td>
                            <td>{{ $data->file_id }}</td>
                            <td>{{ $data->metadata['new_order'] }}</td>
                            <td>{{ $data->metadata['update_order'] }}</td>
                            <td>{{ $data->metadata['cash-total'] }}</td>
                            <td>{{ $data->metadata['cheque-total'] }}</td>
                            <td>{{ $data->metadata['online-total'] }}</td>
                            <td>{{ $data->metadata['total'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="row pull-right mr-4">
                    {!! $files->render() !!}
                </div>
            </div>
        </div>

    </div>
@endsection

@section('footer-js')
<script src="{{ URL::asset('public/css/custom.css') }}"></script>
@endsection
