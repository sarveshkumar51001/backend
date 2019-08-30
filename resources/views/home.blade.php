@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-speedometer"></i><b>Backend Dashboard</b>
        </div>
        <div class="card-body">
            <div class="row col-md-12 justify-content-md-center">
            <a href="{{ route('bulkupload.upload') }}" style="text-decoration: none;">
                <div class="card col-md-12 p-0">
                    <div class="card-body">
                        <img src="{{asset('shopify/shopify-logo.png')}}" alt="shopify">
                    </div>
                    <div class="card-footer text-center">Shopify Bulk Upload</div>
                </div>
            </a>
            </div>
        </div>
        </div>
@endsection
