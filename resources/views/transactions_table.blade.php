<table class="table">
    <thead>
        <tr>
            <th>Activity Name</th>
            <th>Activity Fee</th>
            <th>Location</th>
            <th>Student Enrollment No.</th>
            <th>Student Name</th>
            <th>Transaction amount</th>
            <th>Transaction mode</th>
            <th>Shopify Order Name</th>
            <th>Uploaded By</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
            @if (sizeof($order->payments) == 1)
                <tr>
                    <td>{{$order->activity}}</td>
                    <td>{{$order->activity_fee}}</td>
                    <td>{{$order->student_school_location}}</td>
                    <td>{{$order->school_enrollment_no}}</td>
                    <td>{{$order->student_first_name." ".$order->student_last_name}}</td>
                    <td>{{$order->payments[0]['amount']}}</td>
                    <td>{{$order->payments[0]['mode_of_payment'] }}</td>
                    <td>@isset($order->shopify_order_name) ? {{$order->shopify_order_name}} : Null @endif</td>
                    <td>{{$order->uploaded_by}}</td>
                </tr>
            @else
                @foreach($order->payments as $payment)
                    <tr>
                        <td>{{$order->activity}}</td>
                        <td>{{$order->activity_fee}}</td>
                        <td>{{$order->student_school_location}}</td>
                        <td>{{$order->school_enrollment_no}}</td>
                        <td>{{$order->student_first_name." ".$order->student_last_name}}</td>
                        <td>{{$payment['amount']}}</td>
                        <td>{{$payment['mode_of_payment']}}</td>
                        <td>@isset($order->shopify_order_name) ? {{$order->shopify_order_name}}:Null @endif</td>
                        <td>{{$order->uploaded_by}}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach
        </tbody>
    </table>

