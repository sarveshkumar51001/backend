@extends('admin.app')

@section('content')
    <div class="body">
        <div class="row pull-right m-2">
            <a href="{{ route('bulkupload.upload') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-plus"> &nbsp;</i>New Upload</button></a>
            <a href="{{ route('bulkupload.previous_uploads') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i>Upload History</button></a>
        </div>
        <div class="clearfix mt-2"></div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-sm datatable table-responsive">
                <thead>
                    @foreach(\App\Library\Shopify\Excel::$headerMap as $header)
                        <td><strong>{{ $header }}</strong></td>
                    @endforeach
                </thead>
                <tbody>
                @foreach($records_array as $row)
                    <tr>
                        @foreach(\App\Library\Shopify\Excel::$headerMap as $key => $header)
                            @if(isset($row[$key]))
                                @if(is_array($row[$key]))
                                    <td>
                                        <table>
                                            <thead>
                                            <td>No.</td>
                                            @php $head = array_slice($row[$key], 0, 1) @endphp
                                            @foreach(array_keys($head) as $instKey)
                                                @if(isset(\App\Library\Shopify\Excel::$headerMap[$instKey]))
                                                    <td>{{ $instKey }}</td>
                                                @endif
                                            @endforeach
                                            </thead>

                                            @foreach($row[$key] as $index => $installment)
                                                <tr>
                                                    <td>{{$index}}</td>
                                                    @foreach($installment as $key => $value)
                                                        @if(isset(\App\Library\Shopify\Excel::$headerMap[$key]))
                                                            <td>{{ $value }}</td>
                                                        @endif
                                                    @endforeach
                                                </tr>

                                            @endforeach
                                        </table>
                                    </td>
                                @else
                                    <td class="@if(!empty($errored_data[$row['sno']][$key])) alert-danger @endif ">{{ $row[$key] }}</td>
                                @endif
                            @else
                                <td class="@if(!empty($errored_data[$row['sno']][$key])) alert-danger @endif "></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

