@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-speedometer"></i> Dashboard
        </div>
        <div class="card-body">
            <div class="row align-content col-md-12 row-centered">
            <a href="{{ route('bulkupload.upload') }}">
                <div class="card col-md-12 p-0">
                    <div class="card-body">
                        <img src="{{asset('shopify/shopify-logo.png')}}">
                    </div>
                    <div class="card-footer text-center">Shopify Bulk Upload</div>
                </div>
            </a>
            </div>
        </div>
        </div>
@endsection
