@extends('admin.app')

@section('content')
    <div class="row justify-content-center" >
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">404</h1>
                <h4 class="pt-3">404 Page not Found</h4>
                <p class="text-muted">Sorry, the page you are looking for could not be found.</p>
                <strong>{{ $exception->getMessage() ?? $message ?? '' }}</strong>
            </div>
        </div>
    </div>
@endsection
