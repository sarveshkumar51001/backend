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
                        <td><div>{{ $product->product_id }}</div></td>
                        <td><div>{{ $product->product_name }}</div></td>
                        <td><div>{{ $product->product_category }}</div></td>
                        <td><div>{{ $product->product_tags }}</div></td>
                        <td><div>{{ $product->product_price }}</div></td>
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