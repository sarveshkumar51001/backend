@extends('admin.app')

@section('content')
    <div class="row">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Customer details
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <table class="table table-responsive-sm table-hover table-outline mb-0">
                            <tbody>
                            <tr>
                                <td>name</td>
                                <td>{{ $customer->student_name }}</td>
                            </tr>
                            <tr>
                                <td>dob</td>
                                <td>{{ ($customer->dob) }}</td>
                            </tr>
                            <tr>
                                <td>gender</td>
                                <td>{{ ($customer->gender) }}</td>
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
                    <i class="icon-list"></i>Recommended products
                </div>
                <div class="card-body">
                    <table class="table table-responsive-sm table-hover table-outline mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>product_name</th>
                            <th>recommendaton_type</th>
                            <th>score</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(json_decode($customer_details['recommendations']) as $product)
                            <tr>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->recommendaton_type }}</td>
                                <td>{{ $product->score }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection