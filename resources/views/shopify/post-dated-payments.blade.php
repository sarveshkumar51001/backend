@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Post Dated Payments
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-hover table-outline table-bordered table-striped table-sm datatable no-footer mb-0">
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
                        <td>{{ $payments['activity'] }}</td>
                        <td>{{ $payments['school_enrollment_no'] }}</td>
                        <td><b>{{ $payments['student_name'] }}</b></td>
                        <td>{{ $payments['student_school'] }}</td>
                        <td>{{ $payments['delivery_location'] }}</td>
                        <td>{{ $payments['expected_date'] }}</td>
                        <td>{{ $payments['expected_amount'] }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection