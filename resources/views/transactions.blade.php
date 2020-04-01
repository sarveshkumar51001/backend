@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-list"></i>Transactions
        </div>
        <form method="POST" action="{{ route('get.transactions') }}" enctype="multipart/form-data" onsubmit="form_submit()" id="transaction-form">
            <div class = "card-body">
                @foreach($errors->all() as $key => $value)
                    <div class="alert alert-danger">
                        {{ $value }}
                    </div>
                @endforeach
                <div class="row">
                    @if(is_admin())
                        <div class="col-sm-4">
                        <label><i class="fa fa-address-book" aria-hidden="true"></i> Location*</label>
                            <div class="input-group">
                                <select name="location" class="form-control" required="required">
                                    <option selected="selected" value="">Location </option>
                                    @foreach (App\Models\ShopifyExcelUpload::getBranchNames() as $school)
                                        <option value="{{ $school }}" @if($school == old('location') || $school == request('location')) selected @endif> {{ $school }}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                    @endif
                    <div class="col-sm-4">
                        <label><i class="fa fa-calendar" aria-hidden="true"></i> Txn DateRange*</label>
                            <div class="input-group" style="width:300px;">
                                <span class="input-group-addon"><i class="fa fa-calendar"> Period</i></span>
                                <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-address-book" aria-hidden="true"></i> Reco Status*</label>
                        <div class="input-group">
                            <select name="reco_status" class="form-control" required="required">
                                <option selected="selected" value="">Select </option>
                                @foreach(array_merge(['all'], \App\Models\ShopifyExcelUpload::PAYMENT_RECONCILIATION_STATUS) as $reco_status)
                                    <option value="{{$reco_status}}" @if($reco_status == old('reco_status') || $reco_status == request('reco_status')) selected @endif>{{strtoupper($reco_status)}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{ csrf_field() }}
                    <div class="col-sm-4">
                        <label>&nbsp;</label>
                <div class="input-group">
                    <button type="submit" form="transaction-form" class="btn btn-lg btn-primary mr-3" name="view" value="view">View</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i> &nbsp;Export Transactions</button>
                </div>
                    </div>
                </div>
            </div>
        </form>
        </div>
    @if(!empty($transactions))
        <form method="post" enctype="multipart/form-data" action="#" id="transaction-id-form">
        <table class="table table-bordered table-striped table-sm datatable">
            <thead>

            <tr>
                @if(has_permission(App\Library\Permission::PERMISSION_RECONCILE))
                <th><a onclick="toggle_all('transaction_selected');"> Select Transactions</a></th>
                @endif
                <th>Shopify Order Name</th>
                <th>Transaction Mode</th>
                <th>Transaction Amount</th>
                <th>Reference No(PayTM/NEFT)</th>
                <th>Cheque/DD No</th>
                <th>MICR Code</th>
                <th>Cheque/DD Date</th>
                <th>Drawee Account Number</th>
                <th>Transaction Upload Date</th>
                <th>Reconciliation Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    @if(has_permission(App\Library\Permission::PERMISSION_RECONCILE))
                    <td>
                        <input type="checkbox"  class="transaction_selected" name="transaction_ids[]" value="{{$transaction['Transaction ID']}}">
                    </td>
                    @endif
                    <td>{{$transaction['Shopify Order Name']}}</td>
                    <td>{{$transaction['Transaction Mode']}}</td>
                    <td>{{$transaction['Transaction Amount']}}</td>
                    <td>{{$transaction['Reference No(PayTM/NEFT)']}}</td>
                    <td>{{$transaction['Cheque/DD No']}}</td>
                    <td>{{$transaction['MICR Code']}}</td>
                    <td>{{$transaction['Cheque/DD Date']}}</td>
                    <td>{{$transaction['Drawee Account Number']}}</td>
                    <td>{{$transaction['Transaction Upload Date']}}</td>

                    <td><span class="@if(strtolower($transaction['Reconciliation Status']) == \App\Models\ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_DEFAULT)
                                                        badge badge-warning
                            @elseif(strtolower($transaction['Reconciliation Status']) == \App\Models\ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_SETTLED)
                                                        badge badge-success
                            @elseif(strtolower($transaction['Reconciliation Status']) == \App\Models\ShopifyExcelUpload::PAYMENT_SETTLEMENT_STATUS_RETURNED)
                                                        badge badge-danger
                            @endif">{{ $transaction['Reconciliation Status']}}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
            @if(has_permission(App\Library\Permission::PERMISSION_RECONCILE))
                <button id="settle-button" type="button" class="btn btn-success" onclick="mark_payment_settled();">Mark Settled</button>
            @endif
        </form>
    @elseif(empty($transactions) && isset($transactions))
        <h3 style="color: red"><b>No data found for the given period.</b></h3>
        @endif
@endsection

@section('footer-js')
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script src="{{ URL::asset('js/admin/upload.js') }}"></script>
@endsection
