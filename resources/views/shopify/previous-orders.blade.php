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
                    @foreach(\App\Library\Shopify\Excel::$headerViewMap as $header)
                        <td><strong>{{ $header }}</strong></td>
                    @endforeach
                </thead>
                <tbody>
                @foreach($records_array as $row)
                    <tr>
                        @foreach(\App\Library\Shopify\Excel::$headerViewMap as $key => $header)
                            @if(isset($row[$key]))
                                @if(is_array($row[$key]))
                                    <td>
                                        {{ $key == 'errors' ? json_encode($row[$key]) : count($row[$key]) }}
                                    </td>
                                @else
                                    <td class="@if(!empty($errored_data[$row['sno']][$key])) alert-danger @endif "><span class="

                                    @if($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_PENDING)
                                                badge badge-warning
                                    @elseif($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_COMPLETED)
                                                badge badge-success
                                    @elseif($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_FAILED)
                                                badge badge-danger
                                    @endif
                                    ">
                                        @if($key == 'order_id')
                                            <a target="_blank" href="https://{{ env('SHOPIFY_STORE') }}/admin/orders/{{$row[$key]}}">View <i class="fa fa-external-link"></i></a>
                                            <strong onclick="render_upload_details('{{$row['_id']}}');" class="text-muted aside-menu-toggler"><i class="fa fa-money fa-2x"></i>&nbsp; </strong>
                                        @else
                                            {{ $row[$key] }}
                                        @endif
                                        </span></td>
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

@section('aside-content')
    <div class="tab-pane active" id="timeline" role="tabpanel">
        <div class="callout m-0 py-2 text-muted text-center bg-light text-uppercase">
            <small>
                <strong id="program-title">Payment details <i class="icon-close fa-pull-right aside-menu-toggler"></i></strong>
            </small>
        </div>
        <hr class="transparent mx-3 my-0">
        <div id="payment-details"></div>

    </div>
@endsection

@section('footer-js')
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
    <script>
        _Payload.headers = {!! json_encode(\App\Library\Shopify\Excel::$headerMap)  !!};
    </script>
@endsection

