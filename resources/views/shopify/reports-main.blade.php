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
                <div class="col-sm">
                    <div class="input-group">
                        <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-outline-success" style="position: absolute; bottom: -65px; right: 260px;">View</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
        <a href="{{ route('bulkupload.download_report',['download'=>'pdf']) }}">Download PDF</a>
    </div>
    @if(!empty($report_data))
    <div class="card">
        <table class="table table-bordered table-striped table-sm datatable table-responsive">
            <thead>
            <tr>
                <th>Upload Date</th>
                <th>Enrollment Date</th>
                <th>Student Enrollment No.</th>
                <th>Phone</th>
                <th>Email</th>
                <th>School</th>
                <th>Activity</th>
                <th>Activity Fee</th>
                <th>Scholarship/Discount</th>
                <th>Amount Paid</th>
                <th>Payment Type</th>
            </tr>
            </thead>
            <tbody>
            @foreach($report_data as $row)
                <tr>
                    <td>{{$row['upload_date']}}</td>
                    <td>{{$row['date_of_enrollment']}}</td>
                    <td>{{$row['school_enrollment_no']}}</td>
                    <td>{{$row['mobile_number']}}</td>
                    <td>{{$row['email_id']}}</td>
                    <td>{{$row['school_name']}}</td>
                    <td>{{$row['activity']}} ({{$row['shopify_activity_id']}})</td>
                    <td>{{$row['activity_fee']}}</td>
                    <td>{{$row['scholarship_discount']}}</td>
                    <td>{{array_sum(array_column($row['payments'],'amount'))}}</td>
                    <td>{{$row['order_type']}}</td>
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
