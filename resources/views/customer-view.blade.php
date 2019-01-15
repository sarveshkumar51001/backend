@extends('admin.app')

@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="icon-user"></i>{{ $customer->customer_name }}
                    &nbsp;<span class="badge badge-warning">{{$customer_details['segment']}}</span> |
                    Total Orders:&nbsp;<span class="">{{$customer_details['total_orders']}}</span> |
                    Total Spent:&nbsp;<span class="fa fa-rupee">{{$customer_details['total_spent']}}</span>
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <table class="table table-responsive-sm table-hover table-outline mb-0">
                            <tbody>
                            <tr>
                                <td>school</td>
                                <td>{{ $customer_details['school'] . ' | ' . $customer_details['class']}}</td>
                            </tr>
                            <tr>
                                <td>academic_details</td>
                                <td>{{ json_encode($customer->academic_details) }}</td>
                            </tr>
                            <tr>
                                <td>contact_details</td>
                                <td>{{ json_encode($customer->contact_details) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Previous orders
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>product ID</th>
                            <th>product_name</th>
                            <th>Amount</th>
                            <th>Created at</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->products_details['product_id'] }}</td>
                                    <td><a href="/products/">{{ $order->products_details['product_category'] }} / {{ $order->products_details['product_display_name'] }}</a></td>
                                    <td>{{ $order->products_details['product_price'] }}</td>
                                    <td>{{ $order->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Recommended products
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>product_name</th>
                            <th>Amount</th>
                            <th>recommendation_type</th>
                            <th>score</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($customer_details['recommendations']))
                            @foreach(json_decode($customer_details['recommendations']) as $product)
                                <tr>
                                    <td><a href="/products/{{$product->product_id}}">{{ $product->product_name }}</a></td>
                                    <td>{{ get_product_price($product->product_id) }}</td>
                                    <td>{{ $product->recommendation_type }}</td>
                                    <td>{{ $product->score }}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection