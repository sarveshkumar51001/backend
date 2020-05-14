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

            @if($reco_data['all']['count'] > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <b><i class="fa fa-money"></i> Reconciliation Status</b>
                    </div>
                    <div class="card-body">
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
                    </div>
                </div>
            @endif
            @if($installment_data['all']['count'] > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <b><i class="fa fa-money"></i> Installments Status</b>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="callout callout-info b-t-1 b-r-1 b-b-1">
                                    <small class="text-muted">TOTAL INSTALLMENTS</small><br>
                                    <strong class="h4">{{$installment_data['all']['count']}}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="callout callout-bordered b-t-1 b-r-1 b-b-1">
                                    <small class="text-muted">TOTAL AMOUNT</small><br>
                                    <strong class="h4">₹ {{amount_inr_format($installment_data['all']['amount'])}}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @php
                                $installment_statuses = [
                                    'expired' => 'danger',
                                    'seven_days' => 'warning',
                                    'eight_to_thirty_days' => 'success'
                                    ];
                            @endphp
                            @foreach($installment_statuses as $status => $color)
                                @if($status == 'seven_days')
                                    @php $daterange = sprintf('%s - %s',\Carbon\Carbon::today()->format('m/d/Y'),\Carbon\Carbon::today()->addDays(7)->format('m/d/Y'));@endphp
                                @elseif($status == 'eight_to_thirty_days')
                                    @php $daterange = sprintf('%s - %s',\Carbon\Carbon::today()->addDays(8)->format('m/d/Y'),\Carbon\Carbon::today()->addDays(30)->format('m/d/Y'));@endphp
                                @else
                                    @php $daterange = sprintf('01/01/1970 - %s',\Carbon\Carbon::yesterday()->format('m/d/Y'));@endphp
                                @endif
                                <div class="col-4">
                                    <a href="{{route('bulkupload.installments',['daterange' => $daterange])}}" style="text-decoration:none">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="text-value-lg text-{{$color}} font-weight-bold">{{ round($installment_data[$status ]['count'] / $installment_data['all']['count'] * 100,2) }}% ({{$installment_data[$status]['count']}})</div>
                                            @if($status == 'expired')
                                                <div class="text-muted text-uppercase font-weight-bold small">{{ $status }}</div>
                                            @elseif($status == 'seven_days')
                                                <div class ="text-muted text-uppercase font-weight-bold small">To be collected in next 7 days</div>
                                            @else
                                                <div class="text-muted text-uppercase font-weight-bold small">To be collected in 8-30 days</div>
                                            @endif
                                            <div class="progress progress-xs my-2">
                                                <div class="progress-bar bg-{{$color}}" role="progressbar" style="width: {{round($installment_data[$status]['count'] / $installment_data['all']['count'] * 100,2) }}%" aria-valuenow="{{ round($installment_data[$status]['count'] / $installment_data['all']['count'] * 100,2) }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div><div class="text-value-lg font-weight-bold text-{{$color}}">₹ {{amount_inr_format($installment_data[$status]['amount'])}}</div>
                                        </div>
                                    </div>
                                    </a>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endif
        </div>
        </div>
@endsection
