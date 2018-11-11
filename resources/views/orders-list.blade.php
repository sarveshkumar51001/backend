@extends('layouts.app')

@section('content')
<div class="container">
    <div class="list-group col-md-2" style="float: left">
        <a href="/" class="list-group-item @if(Request::path() == '/') active @endif"><i class="fa fa-tachometer-alt"></i> <span>Dashboard</span></a>
        <a href="/orders" class="list-group-item  @if(Request::path() == 'orders') active @endif"><i class="fa fas fa-list-ul"></i> <span>List orders</span></a>
        <a href="/orders/create" class="list-group-item  @if(Request::path() == 'orders/create') active @endif"><i class="fa fa-plus-circle"></i> <span>Create Orders</span></a>
        <a href="#" class="list-group-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>

    <div class="row justify-content-center col-md-10" style="float: right;">
            <div class="card">
                <div class="card-header">List Orders</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                        As we need a fixed height sidebar, we'll get rid of align-items property that stretched items vertically.

                        However the content extends, the sidebar still will take entire viewport height. For this, we'll replace min-height: 100vh with height: 100vh.
                </div>
            </div>
    </div>
</div>
@endsection
