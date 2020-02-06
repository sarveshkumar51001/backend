@extends('admin.app')

@section('content')
    <div class="col-md-12">

        <div class="row">
            <div class="card col-sm-12">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-5">
                            <h4 class="card-title mb-0">Collection</h4>
                            <div class="small text-muted">{{ request('daterange') ?? 'Today' }}</div>
                        </div>
                        <div class="col-sm-7">
                            <form method="get" action="">
                                <button onclick="download_transactions();" id="download-transactions" type="button" class="btn btn-outline-primary float-right ml-3"><i class="fa fa-download">&nbsp;</i>Export Transactions</button>
                                &nbsp;
                                <button type="submit" class="btn btn-outline-primary float-right">View</button>
                                <fieldset class="form-group float-lg-left">
                                    <div class="input-group float-lg-left" style="width:300px;">
                                        <span class="input-group-addon"><i class="fa fa-calendar"> Period</i></span>
                                        <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <hr class="m-0">
                    <div class="row">
                        @php $total = $total_txn = 0 @endphp
                        @foreach(\App\Models\ShopifyExcelUpload::$modesTitle as $id => $title)
                            @php
                                $total += ($metadata[$id]['total'] ?? 0);
                                $total_txn += ($metadata[$id]['count'] ?? 0);
                            @endphp
                            <div class="col-sm-2">
                                <div class="callout callout-info">
                                    <small class="">{{ str_replace('Online - ', '', $title) }} <span class="badge badge-pill badge-danger">{{ $metadata[$id]['count'] ?? 0 }}</span></small>
                                    <br>
                                    <strong class="h5"><i class="fa fa-rupee">&nbsp;</i>{{ amount_inr_format($metadata[$id]['total'] ?? 0) }}</strong>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-sm-2">
                            <div class="callout callout-success">
                                <small class="text-muted">Total <span class="badge badge-pill badge-danger"> {{ $total_txn }}</span></small>
                                <br>
                                <strong class="h5"><i class="fa fa-rupee">&nbsp;</i>{{ amount_inr_format($total) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="body">
        <div class="row pull-right m-2">
            <a href="{{ route('bulkupload.upload') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-plus"> &nbsp;</i>New Upload</button></a>
            <a href="{{ route('bulkupload.previous_uploads') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i>Upload History</button></a>
            @if(in_array(\Auth::user()->email, \App\Http\Controllers\BulkUpload\ShopifyController::$adminTeam))
                <a href="{{ route('bulkupload.previous_orders') }}?filter=team"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-users"> &nbsp;</i>Team Uploads</button></a>
                <a href="{{ route('orders.transactions') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-money"></i> Transactions</button></a>
            @endif
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
                                        @if($key=='errors' && !empty($row[$key]))
                                            <text style="color:red">{{ $row['errors']['message'] }}</text>
                                        @endif
                                    </td>
                                @else
                                    <td class="@if(!empty($errored_data[$row['sno']][$key])) alert-danger @endif "><span class="

                                    @if($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_PENDING)
                                                badge badge-warning
                                    @elseif($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_COMPLETED)
                                                badge badge-success
                                    @elseif($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_FAILED)
                                                badge badge-danger
                                    @elseif($key == 'job_status' && $row[$key] == \App\Models\ShopifyExcelUpload::JOB_STATUS_PAYMENT_DUE)
                                                badge badge-primary
                                    @endif
                                    ">
                                        @if($key == 'order_id')
                                        <div>
                                            <strong onclick="render_upload_details('{{$row['_id']}}');" class="text-muted aside-menu-toggler" style="cursor: pointer"><a title="Payment Details"><i class="fa fa-money fa-2x"></i></a>&nbsp; </strong>
                                        </div>
                                            @if(!$row['order_id'] == 0 && !isset($row['checkout_url']))
                                            <a target="_blank" href="https://{{ env('SHOPIFY_STORE') }}/admin/orders/{{$row[$key]}}">View <i class="fa fa-external-link"></i></a>
                                                @elseif(!$row['order_id'] == 0 && isset($row['checkout_url']))
                                                    <a target="_blank" href="{{$row['checkout_url']}}">View<i class="fa fa-external-link"></i></a>
                                            @endif
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

