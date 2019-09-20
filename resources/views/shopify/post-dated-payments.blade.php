@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Post Dated Payments
        </div>
        <div class="card-body ">
            @if(count($collection_data) == 0)
                <h4 align="center"><b>No post dated payments found</b></h4>
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
                    <tr>
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
    <script src="{{ URL::asset('public/css/custom.css') }}"></script>
@endsection

