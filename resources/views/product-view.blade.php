@extends('admin.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Product Details
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <tbody>
                        <tr>
                            <td>Product ID</td>
                            <td>{{ $product->id }}</td>
                        </tr>
                        <tr>
                        <tr>
                            <td>Product Name</td>
                            <td>{{ $product->title }}</td>
                        </tr>
                        <tr>
                            <td>Product Type</td>
                            <td>{{ $product->product_type }}</td>
                        </tr>
                        <tr>
                            <td>Product Tags</td>
                            <td>{{ $product->tags }}</td>
                        </tr>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-header">
            <i class="icon-list"></i>Product Variants
            </div>
            <div class="card-body">
                <table class="table table-responsive-sm table-hover table-outline mb-0">
                    <thead class="thead-light">
                <tr>
                    <th>Variant ID</th>
                    <th>Variant Title</th>
                    <th>Variant SKU</th>
                    <th>Variant Price</th>
                </tr>
                </thead>
                    <tbody>
                            @foreach($product->variants as $variant)
                            <tr>
                            <td>{{ $variant['id'] }}</td>
                            <td>{{ ($variant['title'] == 'Default Title' ? $product['title'] : $variant['title']) }}</td>
                            <td>{{ $variant['sku'] }}</td>
                            <td>{{ $variant['price'] }}</td>
                            </tr>
                            @endforeach   
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- <div class="col-md-7">
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
                            <td><a href="{{ url('products/'.$product->id) }}">{{ $product->id }}</a></td>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->product_type}}</td>
                            <td>{{ $product->tags }}</td>
                            <td>{{ $product->product_price }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> -->
    </div>
@endsection