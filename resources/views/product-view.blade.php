@extends('admin.app')

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Products Details
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <tbody>
                        <tr>
                            <td>product_id</td>
                            <td><a href="{{ url('products/'.$product->id) }}">{{ $product->product_id }}</a></td>                        </tr>
                        <tr>
                        <tr>
                            <td>product_name</td>
                            <td>{{ $product->product_name }}</td>
                        </tr>
                        <tr>
                            <td>product_category</td>
                            <td>{{ $product->product_category }}</td>
                        </tr>
                        <tr>
                            <td>product_tags</td>
                            <td>{{ $product->product_tags }}</td>
                        </tr>
                        <tr>
                            <td>product_price</td>
                            <td>{{ $product->product_price }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Recommended Products
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>product_id</th>
                            <th>product_name</th>
                            <th>product_category</th>
                            <th>product_tags</th>
                            <th>product_price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><a href="{{ url('products/'.$product->id) }}">{{ $product->product_id }}</a></td>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ $product->product_category }}</td>
                            <td>{{ $product->product_tags }}</td>
                            <td>{{ $product->product_price }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection