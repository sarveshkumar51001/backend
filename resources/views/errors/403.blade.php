@extends('admin.app')

@section('content')
<div class="row justify-content-center" >
    <div class="col-md-6">
        <div class="clearfix">
            <h1 class="float-left display-3 mr-4">403</h1>
            <h4 class="pt-3">Access denied.</h4>
            <p class="text-muted">Access denied - You are not authorized to access this page.</p>
            <strong>{{ $exception->getMessage() ?? $message ?? '' }}</strong>
        </div>
    </div>
</div>
@endsection
