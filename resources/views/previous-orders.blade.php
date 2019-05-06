@extends('admin.app')
@section('content')
    <div class = "body">
        <p style = "font-weight:bold">Following are the orders successfully created by the excel file provided by you in the past.</p>
        <div class = "card">
            <table class="table table-striped table-bordered table-responsive">
                <thead>
                <tr>
                    @foreach ( ($records_array[0] ?? []) as $key=>$value)
                        <th>{{ $key }}</th>
                @endforeach
                @foreach($records_array as $records)
                    @foreach($records as $key=> $value)
                        @if(is_array($key))
                            @php $val = ($key[0] ?? 0) @endphp
                                    <td> {{ $val }}</td>
                        @elseif (!is_array($key))
                        <tr>
                        <td>{{ $value }}</td>
                        @endif
                        @endforeach
                    </tr>
                @endforeach
            </table>
    </div>
    </div>
@endsection

