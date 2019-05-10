@extends('admin.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> Shopify Bulk Upload<a href="{{ URL::asset('shopify/sample_shopify_file.xlsx') }}"><button style='margin-left:700px' class="btn-info"><i class="fa fa-download"></i> Download sample file</button></a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bulkupload.ShopifyBulkUpload-result') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group row"><label for="date" class="col-md-4 col-form-label text-md-right">Date</label>
                    <div class="col-md-6">
                        <input autocomplete="off" name="date" maxlength="50" type="text" class="form-control datepicker"/>
                    </div>
                </div>
                <div class="form-group row"><label for="end" class="col-md-4 col-form-label text-md-right">Select file</label>
                    <div class="col-md-6">
                        <input autocomplete="off" type="file" name="file" required="required" accept=".xls ,.xlsx" class="form-control">
                        <i id="error-file" class="error text-danger d-none"></i>
                    </div>
                </div>
                <div class="form-group row"><label for="cash-total" class="col-md-4 col-form-label text-md-right">Amount collected by cash</label>
                    <div class="col-md-6">
                        <input autocomplete="off" type="text" name="cash-total" required="required" autofocus="autofocus" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-group row"><label class="col-md-4 col-form-label text-md-right">Amount collected by cheque</label>
                    <div class="col-md-6">
                        <input autocomplete="off" type="text" name="cheque-total" required="required" autofocus="autofocus" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-group row"><label class="col-md-4 col-form-label text-md-right">Amount collected by online</label>
                    <div class="col-md-6">
                        <input autocomplete="off" type="text" name="online-total" required="required" autofocus="autofocus" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-group row mb-0"><div class="col-md-6 offset-md-4"><button style='margin-right:130px' type="submit" class="btn btn-success">
                            Submit
                        </button></div>
                </div>
            </form>
        </div>
    </div>
    <div class ="card">
        <div class ="fa-external-link"><a href="{{ URL::route('bulkupload.List_All_Files') }}"><button style='margin-left:20px' class="btn-info"><i class="fa fa-info"></i>  File Upload History</button></a>
        </div>
    </div>
@endsection

@section('footer-js')
    <script src="{{ URL::asset('vendors/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/datepicker.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script>
        // Load date picker
        $('.datepicker').datepicker().on('changeDate', function(ev) {
            $(this).datepicker('hide');
        });
    </script>
@endsection