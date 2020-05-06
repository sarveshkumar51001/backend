@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i> View Installments by Expected Date
        </div>
    <div class="card-body">
        <form method="get" action="" autocomplete="off">
            <div class="row">
                <div class="col-sm-4">
                    <label><i class="fa fa-calendar" aria-hidden="true"></i> DateRange</label>
                    <div class="input-group" style="width:300px;">
                        <span class="input-group-addon"><i class="fa fa-calendar"> Period</i></span>
                        <input id="daterange" name="search_daterange" class="form-control" required type="text" value="{{old('search_daterange')}}">
                    </div>
                </div>
                <div class="row col-lg mt-4">
                    <button style="height:40px" type="submit" class="btn btn-primary" name="view">View</button>
                </div>
            </div>
        </form>
    </div>
    </div>
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Installments
        </div>
        <div class="card-body ">
            @if(count($collection_data) == 0 && !isset($collection_data))
                <h4 align="center"><b>No Installments due for collection</b></h4>
            @else
            <table class="table table-bordered table-striped table-sm datatable table-responsive">
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
                        <tr style="background-color:#fa8c9d">
                    @elseif(\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::createFromFormat(\App\Models\ShopifyExcelUpload::DATE_FORMAT, $payments['expected_date']), false) <= 7)
                        <tr style="background-color:#f7ef81">
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

