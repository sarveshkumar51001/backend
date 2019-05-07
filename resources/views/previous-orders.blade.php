@extends('admin.app')

@section('content')
    <div class = "body">
        <p style = "font-weight:bold">Following are the orders successfully created by the excel file provided by you in the past.</p>
        <div class = "card">
            <table class="table table-striped table-bordered table-responsive">
                <thead>
                <tr>
                    @foreach (($records_array[0] ?? []) as $key => $value)
                        <th>@if(is_scalar($key)) {{ $key }} @else "Collection data" @endif </th>
                    @endforeach
                </tr>

                @foreach($records_array as $record)
                    <tr>
                    @foreach($record as $key => $value)
                        @if(!is_array($value))
                            <td>{{ $value }}</td>
                        @endif
                    @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection

