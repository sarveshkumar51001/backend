@extends('admin.app')

@section('content')
    <div class="row">
        <div class="card col-sm-12">
            <div class="card-body">
                <form method="get" action="" autocomplete="off">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <h5 class="card-title mb-0">View Installments by Expected Date</h5>
                        </div>
                        <div class="col-sm-7">
                            <div class="input-group pull-right" style="width: 300px">
                                <span class="input-group-addon"><i class="fa fa-calendar"> Period</i></span>
                                <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}" required>
                            </div>
                        </div>
                        <div class="col-sm-1 pull-right">
                            <button style="height:40px" type="submit" class="btn btn-primary pull-right" name="view">View</button>
                        </div>
                    </div>
                </form>
                <hr />
                <div class="row">
                    <div class="col-12 p-0">
                        @if(count($collection_data) == 0 || empty($collection_data))

                            <div class="alert alert-warning text-center">No Installments due for collection for selected period.</div>
                        @else
                            <table class="table table-bordered table-sm datatable table-responsive">
                                <thead>
                                <tr>
                                    <th>Order/File ID</th>
                                    <th>Activity</th>
                                    <th>School Enrollment No</th>
                                    <th>Student Name</th>
                                    <th>Student School</th>
                                    <th>Delivery Location</th>
                                    <th>Expected Collection Date</th>
                                    <th>Expected Collection Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($collection_data as $payments)

                                    @if(\Carbon\Carbon::createFromFormat(\App\Models\ShopifyExcelUpload::DATE_FORMAT,$payments['expected_date'])->timestamp < time())
                                        <tr class="table-danger">
                                    @elseif(\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::createFromFormat(\App\Models\ShopifyExcelUpload::DATE_FORMAT, $payments['expected_date']), false) <= 7)
                                        <tr class="table-warning">
                                    @else
                                        <tr>
                                            @endif
                                            <td>@if(!$payments['order_id'] == 0)
                                                    <a target="_blank" href="https://{{ env('SHOPIFY_STORE') }}/admin/orders/{{$payments['order_id']}}">View <i class="fa fa-external-link"></i></a>
                                                @endif <br>{{ $payments['file_id'] }}</br></td>
                                            <td><b>{{ $payments['activity'] }}</b> ({{ $payments['activity_id']}})</td>
                                            <td>{{ $payments['school_enrollment_no'] }}</td>
                                            <td>{{ $payments['student_name'] }}</td>
                                            <td>{{ $payments['student_school'] }}</td>
                                            <td>{{ $payments['delivery_location'] }}</td>
                                            <td>{{ $payments['expected_date'] }}</td>
                                            <td>{{ $payments['expected_amount'] }}</td>
                                            @endforeach
                                        </tr>
                                </tbody>
                            </table>
                            {{ $collection_data->render() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-js')
    <script>
        $(function() {

            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

        });
    </script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js?v=1.0') }}"></script>
    <script src="{{ URL::asset('public/css/custom.css') }}"></script>
@endsection
