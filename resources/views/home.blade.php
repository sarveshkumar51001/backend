@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-speedometer"></i><b>Backend Dashboard</b>
        </div>
        <div class="card-body">
            <div class="row col-md-12 justify-content-md-center">
                <div class="card col-md-2 m-1 p-0">
                    <div class="card-body text-center">
                        <a href="{{ route('bulkupload.upload') }}" class="stretched-link" style="text-decoration: none;">
                        	<img src="{{asset('shopify/shopify-logo.png')}}" class="img-fluid" alt="shopify">
                    	</a>

                    </div>
                    <div class="card-footer text-center"><a href="{{ route('bulkupload.upload') }}" class="stretched-link" style="text-decoration: none;">Shopify Bulk Upload</a></div>
                </div>
            	<div class="card col-md-2 m-1 p-0">
                    <div class="card-body text-center">
                    	<a href="{{ route('search.students') }}" style="text-decoration: none;" class="stretched-link">
                        	<i class="fa fa-search" aria-hidden="true" style="font-size:150px"></i>
                    	</a>
                    </div>
                    <div class="card-footer text-center"><a href="{{ route('search.students') }}" style="text-decoration: none;" class="stretched-link">Student Search</a></div>
               </div>
                <div class="card col-md-2 m-1 p-0">
                    <div class="card-body text-center">
                        <a href="{{ route('bulkupload.installments') }}" style="text-decoration: none;" class="stretched-link">
                            <i class="fa fa-rupee" aria-hidden="true" style="font-size:150px"></i>
                        </a>
                    </div>
                    <div class="card-footer text-center"><a href="{{ route('bulkupload.installments') }}" style="text-decoration: none;" class="stretched-link">Upcoming Installments</a></div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script>
    function select_load(event)
    {
        window.location = "?year=" + event.value;
        return false;
    }
</script>
