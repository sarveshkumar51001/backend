@extends('admin.app')
@section('content')
    <div class ="body">
        <div class = "card">
            @if(!empty($errored_data))
                <div class="alert-danger p-2">
                    <p style = "font-weight:bold">Following rows of your excel file are erroneous. Please correct before submitting again.</p>
                    <ul>
                        @foreach($errored_data as $key => $value)
                            <li>#{{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="alert alert-success">
                    <?php echo 'Thank You! Your file was successfully uploaded. Your orders will be created in few hours.'; ?>
                </div>
            @endif
            <table class="table table-striped table-bordered table-responsive">
                <tbody>
                    <tr>
                        @foreach(\App\Library\Shopify\Excel::$headerMap as $header)
                            <td><strong>{{ $header }}</strong></td>
                        @endforeach
                    </tr>

                    @foreach($excel_response as $row)
                        <tr @if(!empty($errored_data[$row['sno']])) style="background: yellow;" @endif>
                            @foreach(\App\Library\Shopify\Excel::$headerMap as $key => $header)
                                @if(isset($row[$key]))
                                    @if(is_array($row[$key]))
                                        <td>
                                            <table>
                                                <thead>
                                                <td>No.</td>
                                                @php $head = reset($row[$key]) @endphp
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
            <div class="pull-left">
                <a href="/bulkupload">
                    <button class="btn btn-lg btn-success">Go Back</button>
                </a>
            </div>
        </div>
    </div>
@endsection
