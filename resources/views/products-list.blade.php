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
                    <th>Product ID</th>
                    <th>Variant ID</th>
                    <th>Product SKU</th>
                    <th>Product Name</th>
                    <th>Product Type</th>
                    <th>Product Tags</th>
                    <th>Product Price</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
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
                        </tr>
                        @endforeach
                    @endif
                @endforeach
                </tbody>
            </table>
            <div class="row pull-right mr-4">
                {!! $products->render() !!}
            </div>
        </div>
    </div>
@endsection