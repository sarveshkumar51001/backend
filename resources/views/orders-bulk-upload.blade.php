@extends('admin.app')
@section('content')
    @php $flag_msg = $flag_msg ?? 0 @endphp
    @php $flag_msg1 = $flag_msg1 ?? 0 @endphp
    @php $flag_msg2 = $flag_msg2 ?? 0 @endphp
    @php $flag_msg3 = $flag_msg3 ?? 0 @endphp
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> Shopify Bulk Upload<a href="{{ URL::asset('shopify/sample_shopify_file.xlsx') }}"><button style='margin-left:700px' class="btn-info"><i class="fa fa-download"></i> Download sample file</button></a>
        </div>
        <div class="card-body">
            @if( $flag_msg == App\Models\Shopify::STATUS_SUCCESS )
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <div class="alert alert-success">
                    <?php echo 'Thank You!Your file was successfully uploaded.'; ?>
                    </div>
                </div>
            </div>
            @endif
                @if ($flag_msg1 == App\Models\Shopify::STATUS_CASH_FAILURE )
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <div class="alert alert-danger">
                            <?php echo 'The amount collected by cash does not match with the excel file uploaded.'; ?>
                        </div>
                    </div>
                </div>
            @endif
                @if ($flag_msg2 == App\Models\Shopify::STATUS_CHEQUE_FAILURE )
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <div class="alert alert-danger">
                            <?php echo 'The amount collected by cheque does not match with the excel file uploaded.'; ?>
                        </div>
                    </div>
                </div>
            @endif
                @if ($flag_msg3 == App\Models\Shopify::STATUS_ONLINE_FAILURE )
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <div class="alert alert-danger">
                            <?php echo 'The amount collected by online does not match with the excel file uploaded.'; ?>
                        </div>
                    </div>
                </div>
            @endif
            <form method="POST" action="{{ route('bulkupload.ShopifyBulkUpload-result') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group row"><label for="date" class="col-md-4 col-form-label text-md-right">Date</label>
                    <div class="col-md-6">
                        <input id="name" type="date" name="date" required="required" autofocus="autofocus" class="form-control" placeholder="dd/mm/yyyy">
                    </div>
                </div>
                <div class="form-group row"><label for="end" class="col-md-4 col-form-label text-md-right">Select file</label>
                    <div class="col-md-6">
                        <input type="file" name="file" required="required" accept=".xls ,.xlsx" class="form-control">
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

