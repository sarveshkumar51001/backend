@extends('admin.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> Shopify Bulk Upload 
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bulkupload.ShopifyBulkUpload-result') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group row"><label for="date" class="col-md-4 col-form-label text-md-right">Date</label>
                    <div class="col-md-6">
                        <input id="name" type="text" name="date" required="required" autofocus="autofocus" class="form-control" placeholder="dd/mm/yyyy">
                    </div>
                </div>
                <div class="form-group row"><label for="end" class="col-md-4 col-form-label text-md-right">Select file</label>
                    <div class="col-md-6">
                        <input type="file" name="file" required="required" class="form-control">
                        <i id="error-file" class="error text-danger d-none"></i>
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
@endsection

