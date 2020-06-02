@extends('admin.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-search"></i>  Search result of <strong>"{{ $query ?? '' }}"</strong>
                    <ul class="nav nav-tabs float-right" role="tablist">
                        <li class="nav-item">
                            <a tab="" class="nav-link @if(count($result['customers'])) active @endif" data-toggle="tab" href="#customers" role="tab">Customers
                                <span class="badge badge-pill badge-success">{{ count($result['customers']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a tab="" class="nav-link @if(count($result['products']) && !count($result['customers']) && !count($result['orders']) ) active @endif" data-toggle="tab" href="#products" role="tab">Products
                                <span class="badge badge-pill badge-success">{{ count($result['products']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a tab="" class="nav-link @if(count($result['orders']) && !count($result['customers']) && !count($result['products']) ) active @endif" data-toggle="tab" href="#orders" role="tab">Orders
                                <span class="badge badge-pill badge-success">{{ count($result['orders']) }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane @if(count($result['customers'])) active  @endif" id="customers">
                            <table class="table table-bordered table-striped table-sm datatable">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Organization</th>
                                    <th>Program</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($result['customers']))
                                    @foreach($result['customers'] as $customer)
                                        <tr>
                                            <td>{{$loop->index + 1}}</td>
                                            <td><a href="{{ url('customers/'.$customer->id) }}">{{ $customer->customer_name }}</a></td>
                                            <td>{{ $customer->customer_id }}</td>
                                            <td>{{ $customer->contact_details['contact_no'] }} | {{ $customer->contact_details['contact_email'] }}</td>
                                            <td>{{ $customer->academic_details['school_name'] }}</td>
                                            <td>{{ $customer->academic_details['class'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="18">No record found.</td></tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane @if(count($result['products']) && !count($result['customers']) && !count($result['orders']) ) active @endif" id="products">
                            <table class="table table-bordered table-striped table-sm datatable">
                                <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Variant ID</th>
                                    <th>Variant SKU</th>
                                    <th>Variant Title</th>
                                    <th>Category</th>
                                    <th>Tags</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                    				<th>Status</th>
                    				<th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($result['products']))
                                    @foreach($result['products'] as $product)
                                    @if (sizeof($product->variants) == 1)
                                        <tr>
                                        <td>{{$product->id}}</td>
                                            @foreach($product->variants as $variant)
                                                <td>{{$variant['id']}}</td>
                                                <td>{{$variant['sku'] }}</td>
                                                 <td>{{ ($variant['title'] == 'Default Title' ? $product['title'] : $variant['title']) }}</td>
                                                <td>{{$product->product_type}}</td>
                                                <td>{{$product->tags}}</td>
                                                <td>{{$variant['price']}}</td>
                                                <td class="font-weight-bold">@if($variant['inventory_quantity'] > 0 || empty($variant['inventory_management'])) <p class="text-success">In Stock</p> @else  <p class="text-danger">Out of Stock</p>@endif</td>
                                                <td>@if($product['published_at'] != null) <p class="text-success">Enabled</p> @else <p class="text-danger">Disabled</p> @endif</td>
                                                <td><a target="_blank" href="/online/products/move/{{$variant['id']}}">Move To Online</a></td>
                                            @endforeach
                                        </tr>
                                @else
                                    @foreach($product->variants as $variant)
                                        <tr>
                                        <td>{{$product->id}}</td>
                                        <td>{{$variant['id']}}</td>
                                        <td>{{$variant['sku']}}</td>
                                        <td>{{$product['title'] ."(". $variant['title'].")"}}</td>
                                        <td>{{$product->product_type}}</td>
                                        <td>{{$product->tags}}</td>
                                        <td>{{$variant['price']}}</td>
                                       <td class="font-weight-bold">@if($variant['inventory_quantity'] > 0 || empty($variant['inventory_management'])) <p class="text-success">In Stock</p> @else  <p class="text-danger">Out of Stock</p> @endif</td>
                						<td class="font-weight-bold">@if($product['published_at'] != null) <p class="text-success">Enabled</p> @else <p class="text-danger">Disabled</p> @endif</td>
                                            <td>
                                                <a class="btn btn-warning" target="_blank" href="/online/products/move/product/{{$product['id']}}">Move Full product to Online</a>
                                                <hr/><a  class="btn btn-success" target="_blank" href="/online/products/move/variant/{{$variant['id']}}">Move Only Variant to Online</a></td>
                                        </tr>
                                    @endforeach
                                    @endif
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane @if(count($result['orders']) && !count($result['customers']) && !count($result['products']) ) active @endif" id="orders">
                            <table class="table table-bordered table-striped table-sm datatable">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($result['orders']))
                                    @foreach($result['orders'] as $order)
                                        <tr>
                                            <td><a href="{{ url('orders/'.$order->id) }}">{{ $order->order_id }}</a></td>
                                            <td>{{ $order->{'student name'} }}</td>
                                            <td>{{ $order->products_details['product_display_name'] }}</td>
                                            <td>{{ $order->products_details['product_price'] }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
