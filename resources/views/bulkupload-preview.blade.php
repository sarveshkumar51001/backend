@extends('admin.app')
@section('content')
    <div class ="body">
        <div class = "card">
            @if(!empty($errored_data))
                <div class="alert-danger p-2">
                    <p style = "font-weight:bold">Following rows of your excel file are erroneous. Please correct before submitting again.</p>
                    <ul>
                        @foreach($errored_data as $key => $value)
                            <li>{{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <table class="table table-striped table-bordered table-responsive">
                <tbody>
                    <tr>
                        @foreach($headers as $header)
                            <td><strong>{{ $header }}</strong></td>
                        @endforeach
                            <td><strong>Installments</strong></td>
                    </tr>

                    @foreach($excel_response as $row)
                        <tr>
                            @foreach($row as $value)
                                <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
