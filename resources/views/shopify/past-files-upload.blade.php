@extends('admin.app')
@section('content')
    <div class = "body">
        <p style = "font-weight:bold">Following are the excel files uploaded by you in the past.</p>
        <div class = "card">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Upload Date</th>
                <th>Uploaded File</th>
            </tr>
            </thead>
            <tbody>
            @foreach( $files as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td><a href="{{ URL::asset($value) }}">
                            <div style="height:100%;width:100%">
                                Excel File
                            </div>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>
    </div>
@endsection
