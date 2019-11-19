@extends('admin.app')

@section('content')

    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <style>
            @page {
                margin: 0.25cm;
                margin-bottom: 1cm;
            }
            table {
                border-collapse: collapse;
            }

            .footer {
                position: relative;
                right: 0;
                left: 0;
                bottom: 0;
                width: 100%;
                text-align: center;
            }
            .header {
                position: relative;
                top: 0px;
                text-align: center;
            }
            .pagenum:before {
                content: counter(page);
            }
            thead { display: table-header-group }
            tfoot { display: table-row-group }
            tr { page-break-inside: avoid }
        </style>
    </head>

    <body style="background-color: white; font-size:14pt;">
    <div>

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

    @endsection
