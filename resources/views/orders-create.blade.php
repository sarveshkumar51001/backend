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
                <div class="card-header">Create Orders</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>
                        You can upload the orders data in given format, <a href="#">Download sample file</a>. It will automatically
                        create orders, customers on shopify in some time using API.
                    </p>

                    <form method="POST" action="/orders/create">
                        @csrf
                        <div class="form-group row"><label for="date" class="col-md-4 col-form-label text-md-right">Date</label>
                            <div class="col-md-6">
                                <input id="name" type="text" name="date" required="required" autofocus="autofocus" class="form-control" placeholder="dd/mm/yyyy">
                            </div>
                        </div>
                        <div class="form-group row"><label for="end" class="col-md-4 col-form-label text-md-right">Select file</label>
                            <div class="col-md-6">
                                <input type="file" name="file" required="required" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row"><label for="cash-total" class="col-md-4 col-form-label text-md-right">Amount collected by cash</label>
                            <div class="col-md-6">
                                <input id="name" type="text" name="cash-total" required="required" autofocus="autofocus" class="form-control" >
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-md-4 col-form-label text-md-right">Amount collected by cheque</label>
                            <div class="col-md-6">
                                <input id="name" type="text" name="cheque-total" required="required" autofocus="autofocus" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-md-4 col-form-label text-md-right">Amount collected by online</label>
                            <div class="col-md-6">
                                <input id="name" type="text" name="online-total" required="required" autofocus="autofocus" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row mb-0"><div class="col-md-6 offset-md-4"><button type="submit" class="btn btn-success">
                                    Save
                                </button></div></div>
                    </form>

                </div>
            </div>
    </div>
</div>
@endsection
