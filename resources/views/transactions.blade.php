@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Transactions
        </div>
        @if(session('message'))
            <div class="alert alert-info" role="alert">
                <p class="m-0">{{ session('message')  }}</p>
            </div>
        @endif
        <form method="POST" action="{{ route('get.transactions') }}" enctype="multipart/form-data" onsubmit="form_submit()">
            {{ csrf_field() }}
            <div class = "card-body">
                @foreach($errors->all() as $key => $value)
                    <div class="alert alert-danger">
                        {{ $value }}
                    </div>
                @endforeach
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label><i class="fa fa-address-book" aria-hidden="true"></i> Location*</label>
                            <div class="input-group">
                                <select name="location" class="form-control select2" required="required">
                                    <option selected="selected" value="">Location </option>
                                    @foreach (array_merge(['All'],App\Models\ShopifyExcelUpload::getBranchNames()) as $school)
                                        <option value="{{ $school }}" @if($school == old('location') || $school == request('location')) selected @endif> {{ $school }}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-calendar" aria-hidden="true"></i> Txn DateRange*</label>
                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"> Period</i></span>
                                <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-address-book" aria-hidden="true"></i> Reco Status*</label>
                        <div class="form-group input-group">
                            <select name="reco_status" class="form-control select2" required="required">
                                <option selected="selected" value="">Select </option>
                                @foreach(array_merge(['all'], \App\Models\ShopifyExcelUpload::PAYMENT_RECONCILIATION_STATUS) as $reco_status)
                                    <option value="{{$reco_status}}" @if($reco_status == old('reco_status') || $reco_status == request('reco_status')) selected @endif>{{strtoupper($reco_status)}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label><i class="fa fa-product-hunt" aria-hidden="true"></i> Activity</label>
                        <div class="form-group input-group">
                            <select id="js-example-basic-multiple" class="form-control" name="activity_list[]" multiple="multiple" style="width:100%">

                                @foreach(array_merge(['All'],$products) as $product)
                                    <option value="{{$product}}" @if(!empty(request('activity_list')) && in_array($product, request('activity_list'))) selected @endif>{{$product}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-sm-4">
                        <label><i class="fa fa-info-circle" aria-hidden="true"></i> Include Unpaid Installment?</label>
                        <div class="form-group input-group"style="position: absolute;top: 40%;left: 25%">
                            <label class="switch switch-icon switch-pill switch-success">
                                <input type="checkbox" class="switch-input" name="unpaid_active" id="active" @if(request('unpaid_active') == 'on') checked @endif>
                                <span class="switch-label" data-on="" data-off=""></span>
                                <span class="switch-handle"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-sm-2" style="padding-top: 20px">
                        <div class="input-group">
                            <button id="file-download-btn" type="submit" class="btn btn-primary"><i class="fa fa-download"></i> &nbsp;Export Transactions</button>
                            <a href="/transactions" id="close-button" class="btn btn-danger"><i class="fa fa-close"></i> &nbsp;Clear</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
@endsection

@section('footer-js')
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#js-example-basic-multiple').select2({
                theme:'bootstrap',
                placeholder: 'Select activity',
                maximumSelectionLength: 5
            });
        });
    </script>
@endsection
