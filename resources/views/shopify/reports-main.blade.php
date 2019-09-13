@extends('admin.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <i class="fa fa-file"></i>Reports
        </div>
    <form method="POST" action="{{ route('bulkupload.render_reports') }}" enctype="multipart/form-data" onsubmit="form_submit()">
        <div class = "card-body">
            <div class="row">
                <div class="col-sm-4">
                    <label><i class="fa fa-tag">Report Type</i></label>
                    <div class="input-group">
                        <select name="report-type" class="form-control" required="required">
                            <option selected="selected" value="">Select Report Type </option>
                            <option value="RM Enrollment Report">RM Enrollment Report</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label><i class="fa fa-calendar"> Period</i></label>
                        <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                        </div>
                {{ csrf_field() }}
                <div class="col-sm-4">
                    <div class="input-group">
                        <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-primary"><i class="fa fc-agenda-view"></i>View</button>
                    </div>
                </div>
                <div class="col-sm-4">
                <a href="{{ URL::asset('shopify/sample_shopify_file.xls') }}"><button type="button" class="btn-info"><i class="fa fa-file-excel-o"></i>
                    </button></a>
                </div>
            </div>
        </div>
    </form>
    </div>
    {{ $report_pdf }}
    @if(!empty($report_data))
    <div class="card">
        <table class="table table-bordered table-striped table-sm datatable table-responsive">
            <thead>
            @foreach(\App\Library\Shopify\Excel::$headerViewMap as $header)
                <td><strong>{{ $header }}</strong></td>
            @endforeach
            </thead>
            <tbody>
            @foreach($report_data as $row)
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
                                    @endif ">
                                        @if($key == 'order_id')
                                            <div>
                                            <strong onclick="render_upload_details('{{$row['_id']}}');" class="text-muted aside-menu-toggler" style="cursor: pointer"><a title="Payment Details"><i class="fa fa-money fa-2x"></i></a>&nbsp; </strong>
                                        </div>
                                            @if(!$row['order_id'] == 0)
                                                <a target="_blank" href="https://{{ env('SHOPIFY_STORE') }}/admin/orders/{{$row[$key]}}">View <i class="fa fa-external-link"></i></a>
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
    @endif
    @endsection

@section('footer-js')
    <script src="{{ URL::asset('js/views/datepicker.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
@endsection
