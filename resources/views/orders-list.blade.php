@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-user"></i>Orders
        </div>
        <div class="card-body">
            <table class="table table-responsive-sm table-hover table-outline mb-0">
                <thead class="thead-light">
                <tr>
                    <th>order_id</th>
                    <th>student_name</th>
                    <th>contact_details</th>
                    <th>products_details</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><div>{{ $order->order_id }}</div></td>
                        <td><div>{{ $order->{'student name'} }}</div></td>
                        <td><div>{{ json_encode($order->contact_details) }}</div></td>
                        <td><div>{{ json_encode($order->products_details) }}</div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="row pull-right mr-4">
                {!! $orders->render() !!}
            </div>
        </div>
    </div>
@endsection