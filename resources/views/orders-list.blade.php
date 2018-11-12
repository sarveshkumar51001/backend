@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> List orders
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered datatable">
                <thead>
                <tr>
                    <th>Organization</th>
                    <th>Bill Prefix</th>
                    <th>Bill Series</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection