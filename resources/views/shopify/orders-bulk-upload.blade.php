@extends('admin.app')
@section('content')
    
    <div class="card">
        <div class="card-header">
            <i class="fa fa-cloud-upload"></i>Bulk Upload
            <div class="row pull-right">
                <a href="{{ route('bulkupload.previous_uploads') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i> File Upload History</button></a>
                <a href="{{ route('bulkupload.previous_orders') }}"><button type="button" class="btn btn-outline-success btn-sm ml-2"><i class="fa fa-list"> &nbsp;</i>Previous Orders</button></a>
                <a href="{{ URL::asset('shopify/sample_shopify_file.xls') }}"><button type="button" class="btn btn-outline-primary btn-sm ml-2"><i class="fa fa-download"> &nbsp;</i>Download sample file</button></a>
            </div>
        </div>
        <div class="card-body">
            @foreach($errors->all() as $key => $value)
                <div class="alert alert-danger">
                    {{ $value }}
                </div>
            @endforeach
            <form method="POST" action="{{ route('bulkupload.upload_preview') }}" enctype="multipart/form-data">
                <div class="card">
            		<div class="card-header">
            			Amount Collected
            		</div>
                 	<div class="card-body">
                          <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><i class="fa fa-money" aria-hidden="true"></i> In Cash</label>
                                    <input autocomplete="off" type="text" name="cash-total" required="required" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label><i class="fa fa-university" aria-hidden="true"></i> By Cheque</label>
                                <div class="input-group">
                                    <input autocomplete="off" type="text" name="cheque-total" required="required" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <label><i class="fa fa-globe" aria-hidden="true"></i> Online</label>
                                <div class="input-group">
                                    <input autocomplete="off" type="text" name="online-total" required="required" class="form-control" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                	<div class="col-sm-4">
                        <div class="form-group">
                            <label><i class="fa fa-calendar"></i> Select Date</label>
                                <input autocomplete="off" name="date" maxlength="50" type="text" class="form-control datepicker" required value="{{ date('d/m/Y') }}"/>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-file-excel-o" aria-hidden="true"></i> Upload file (only .xls files allowed)</label>
                        <input type="file" name="file" required="required" accept=".xls" class="form-control">
                    </div>
                    {{ csrf_field() }}
                    <div class="col-sm-4">
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
        $('.datepicker').datepicker({format: 'dd/mm/yyyy'}).on('changeDate', function(ev) {
            $(this).datepicker('hide');
        });
    </script>
@endsection