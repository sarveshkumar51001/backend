@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Products
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
                @foreach($products as $product)
                    <tr>
                        <td><a href="{{ url('products/'.$product->id) }}">{{ $product->product_id }}</a></td>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->product_category }}</td>
                        <td>{{ $product->product_tags }}</td>
                        <td>{{ $product->product_price }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="row pull-right mr-4">
                {!! $products->render() !!}
            </div>
        </div>
    </div>
@endsection