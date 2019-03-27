@extends('admin.app')
@section('content')
    <div class ="body">
    <p style = "font-weight:bold">Following rows of your excel file are erroneous. Please correct before submitting again.</p>
        <div class = "card">
        <table class="table table-striped table-bordered table-responsive">
            <thead>
            <tr>
                @foreach($errored_data[0] as $key => $value)
                    <th>{{ $key }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @php $counter = 0; @endphp
            @foreach($errored_data as $key => $value)
                <tr>
                    @foreach($value as $header => $row_val)
                        <td @if(array_key_exists($header, $excel_response[$counter])) style="background-color:#ff3333;color: #fff;font-weight: bold;" title = "{{ $excel_response[$counter][$header][0] }}" @endif>{{ $row_val }}</td>
                    @endforeach
                </tr>
                @php $counter ++; @endphp
            @endforeach
            </tbody>
            </table>
    </div>
    </div>
@endsection
