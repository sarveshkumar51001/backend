@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Transactions
        </div>
        <form method="POST" action="{{ route('get.transactions') }}" enctype="multipart/form-data" onsubmit="form_submit()">
            <div class = "card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <label><i class="fa fa-address-book" aria-hidden="true"></i> Location*</label>
                            <div class="input-group">
                                <select name="location" class="form-control" required="required">
                                    <option selected="selected" value="">Location </option>
                                    @foreach (App\Models\ShopifyExcelUpload::getBranchNames() as $school)
                                        <option value="{{ $school }}" @if($school == old('school-name')) selected @endif> {{ $school }}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-calendar" aria-hidden="true"></i> Txn DateRange*</label>
                            <div class="input-group" style="width:300px;">
                                <span class="input-group-addon"><i class="fa fa-calendar"> Period</i></span>
                                <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            </div>
                    </div>
                    {{ csrf_field() }}
                    <div class="col-sm-4">
                        <label>&nbsp;</label>
                <div class="input-group">
                    <button id="file-download-btn" type="submit" class="btn btn-primary"><i class="fa fa-download"></i> &nbsp;Export All Transactions</button>
                </div>
                    </div>
                </div>
            </div>
        </form>
        </div>
    @if(empty($order_data) && isset($order_data))
        <h4><b>No data found for the date range/ location selected.</b></h4>
    @endif
@endsection

@section('footer-js')
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
    <script>
        _Payload.headers = {!! json_encode(\App\Library\Shopify\Excel::$headerMap)  !!};
    </script>
@endsection
