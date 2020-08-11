@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="icon-speedometer"></i><b>Backend Dashboard</b>
        </div>
        <div class="card-body">
            <div class="row col-md-12 justify-content-md-center">
                <div class="card col-md-2 m-1 p-0">
                    <div class="card-body text-center">
                        <a href="{{ route('bulkupload.upload') }}" class="stretched-link" style="text-decoration: none;">
                        	<img src="{{asset('shopify/shopify-logo.png')}}" class="img-fluid" alt="shopify">
                    	</a>

                    </div>
                    <div class="card-footer text-center"><a href="{{ route('bulkupload.upload') }}" class="stretched-link" style="text-decoration: none;">Shopify Bulk Upload</a></div>
                </div>
            	<div class="card col-md-2 m-1 p-0">
                    <div class="card-body text-center">
                    	<a href="{{ route('search.students') }}" style="text-decoration: none;" class="stretched-link">
                        	<i class="fa fa-search" aria-hidden="true" style="font-size:150px"></i>
                    	</a>
                    </div>
                    <div class="card-footer text-center"><a href="{{ route('search.students') }}" style="text-decoration: none;" class="stretched-link">Student Search</a></div>
               </div>
                <div class="card col-md-2 m-1 p-0">
                    <div class="card-body text-center">
                        <a href="{{ route('bulkupload.installments') }}" style="text-decoration: none;" class="stretched-link">
                            <i class="fa fa-rupee" aria-hidden="true" style="font-size:150px"></i>
                        </a>
                    </div>
                    <div class="card-footer text-center"><a href="{{ route('bulkupload.installments') }}" style="text-decoration: none;" class="stretched-link">Upcoming Installments</a></div>
                </div>
            </div>

            @if( isset( $Users['permissions'] ) && ( in_array('admin', $Users['permissions'])
                || in_array('reconcile', $Users['permissions']))
                )

                <div class="card mt-4">
                    <div class="card-header">
                        <b><i class="fa fa-money"></i> Reconciliation Status</b>
                    </div>

                    <div class="card-body">
                        @if(empty(request('daterange')))
                            <span class="row alert alert-info">
                                <i class="icon-info"></i>&nbsp;&nbsp;This data is showing for current financial Year
                            </span>
                        @endif
                        @if($reco_data['all']['count'] > 0)
                            <div class="row">
                                <div class="col-md-6">
                                    <form method="GET" action="{{ route('home') }}">
                                        <div class="form-group input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"> Daterange</i></span>
                                            <input id="txn_range" name="daterange" class="form-control date-picker" type="text">
                                            <button  class="btn btn-success" style="margin-top: 0px;" type="submit">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-3">
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ URL::to('/transactions') }}" style="float: right;margin-left: 10px;margin-bottom: 20px;" class="btn btn-success">Transaction</a>
                                    <a href="{{ URL::to('/shopify/reconcile') }}" style="float: right" class="btn btn-primary">Reconcile</a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="callout callout-info b-t-1 b-r-1 b-b-1">
                                        <small class="text-muted">TOTAL TRANSACTIONS</small><br>
                                        <strong class="h4">{{$reco_data['all']['count']}}</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="callout callout-bordered b-t-1 b-r-1 b-b-1">
                                        <small class="text-muted">TRANSACTION AMOUNT</small><br>
                                        <strong class="h4">₹ {{amount_inr_format($reco_data['all']['amount'])}}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @php
                                    $reco_statuses = [
                                        'pending' => 'warning',
                                        'settled' => 'success',
                                        'returned' => 'danger'
                                        ];
                                @endphp
                                @foreach($reco_statuses as $status => $color)
                                <div class="col-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="text-value-lg text-{{$color}} font-weight-bold">{{ round($reco_data[$status ]['count'] / $reco_data['all']['count'] * 100,2) }}% ({{$reco_data[$status]['count']}})</div>
                                            <div class="text-muted text-uppercase font-weight-bold small">{{ $status }} Transactions</div>
                                            <div class="progress progress-xs my-2">
                                                <div class="progress-bar bg-{{$color}}" role="progressbar" style="width: {{round($reco_data[$status]['count'] / $reco_data['all']['count'] * 100,2) }}%" aria-valuenow="{{ round($reco_data[$status]['count'] / $reco_data['all']['count'] * 100,2) }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div><div class="text-value-lg font-weight-bold text-{{$color}}">₹ {{amount_inr_format($reco_data[$status]['amount'])}}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            </div>
                        @else
                            <div class="row alert alert-danger">
                                Records Not Found
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            </div>
        </div>
@endsection
<script>
    function select_load(event)
    {
        window.location = "?year=" + event.value;
        return false;
    }
</script>
