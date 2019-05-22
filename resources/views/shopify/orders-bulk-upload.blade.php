@extends('admin.app')
@section('content')

    <div class="card">
        <div class="card-header">
            <i class="fa fa-cloud-upload"></i>Bulk Upload
            <div class="row pull-right">
                <a href="{{ route('bulkupload.previous_uploads') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i>Upload History</button></a>
                <a href="{{ route('bulkupload.previous_orders') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i>Previous Orders</button></a>
                <a href="{{ URL::asset('shopify/sample_shopify_file.xls') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-download"> &nbsp;</i>Download sample file</button></a>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bulkupload.upload_preview') }}" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Select Date</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input autocomplete="off" name="date" maxlength="50" type="text" class="form-control datepicker" value="{{ date('m/d/Y') }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Total amount by cash: </label>
                            <input autocomplete="off" type="text" name="cash-total" required="required" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>Total amount by cheque:</label>
                        <div class="input-group">
                            <input autocomplete="off" type="text" name="cheque-total" required="required" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <label>Total amount by online:</label>
                        <div class="input-group">
                            <input autocomplete="off" type="text" name="online-total" required="required" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Select file </label>
                            <input autocomplete="off" type="file" name="file" required="required" accept=".xls ,.xlsx" class="form-control">
                        </div>
                    </div>
                    {{ csrf_field() }}
                    <div class="col-sm-3">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-upload"></i> &nbsp; Upload</button>
                        </div>
                    </div>
                </div>
            </form>
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