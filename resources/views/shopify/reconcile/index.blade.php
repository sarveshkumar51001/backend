@extends('admin.app')
@section('content')
    <div class="body">
        @if(has_permission('reconcile'))
            <h4 class="text-center">Reconciliation Status</h4>
            <div class="card mt-4">
                <div class="card-body">
                    @if(!empty($range))
                        <span class="row alert alert-info">
                                <i class="icon-info"></i>&nbsp;&nbsp;Currently showing result for period [{{$range}}]
                            </span>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <form method="GET" action="/shopify/reconcile">
                                <div class="form-group input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"> Filter by date range</i></span>
                                    <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{request('daterange')}}">
                                    <button  class="btn btn-success" style="margin-top: 0px;" type="submit">View</button>
                                    <a href="{{ url()->current() }}"  class="btn btn-danger" style="margin-top: 0px;" type="submit">Reset</a>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3">
                        </div>
                        <div class="col-md-3">
                            <a href="{{ URL::to('/transactions') }}" style="float: right;margin-left: 10px;margin-bottom: 20px;" class="btn btn-warning"> <i class="fa fa fa-download"></i> Transaction</a>
                        </div>
                    </div>
                    @if($reco_data['all']['count'] > 0)

                        <div class="row">
                            <div class="col-6">
                                <div class="callout callout-info b-t-1 b-r-1 b-b-1">
                                    <small class="text-muted">TOTAL TRANSACTIONS</small><br>
                                    <strong class="h4">{{$reco_data['all']['count']}} <span class="h6 text-muted">PAID</span></strong>
                                    @if(!empty($reco_data['all']['pdc_count']))<strong class="h4"> / {{$reco_data['all']['pdc_count']}} <span class="h6 text-muted">PDC</span></strong>@endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="callout callout-bordered b-t-1 b-r-1 b-b-1">
                                    <small class="text-muted">TRANSACTION AMOUNT</small><br>
                                    <strong class="h4">₹ {{amount_inr_format($reco_data['all']['amount'])}} <span class="h6 text-muted">PAID</span></strong>
                                    @if(!empty($reco_data['all']['pdc_amount']))<strong class="h4"> / {{amount_inr_format($reco_data['all']['pdc_amount'])}} <span class=" h6 text-muted">PDC</span></strong>@endif
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
            <h4 class="text-center">Reconcile</h4>

            <div class = "card">

            <div class="card-body">
                @foreach($errors->all() as $key => $value)
                    <div class="alert alert-danger">
                        {{ $value }}
                    </div>
                @endforeach
                <form enctype="multipart/form-data" method="post" action="{{ route('bulkupload.reconcile.preview') }}" id="reconcile-file-form" >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Statement source</label>
                                    <select class="form-control" name="source" required>
                                        <option value="">Select...</option>
                                        @foreach(\App\Library\Shopify\Reconciliation\File::$sourceTitles as $code => $title)
                                            <option value="{{$code}}">{{$title}}</option>
                                        @endforeach
                                    </select>
                                    <i id="source" class="error text-danger d-none"></i>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Select statement file </label>
                                    <input id="file" name="file" type="file" class="form-control form-control-sm" required>
                                    <i id="error-file" class="error text-danger d-none"></i>
                                </div>
                            </div>
                            <div class="col-sm-4 pull-right">
                                <label>&nbsp;</label>
                                <div class="input-group">
                                    <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-upload"></i> Upload</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ csrf_field() }}
                </form>
            </div>
        </div>
        <div class="clearfix"></div>
        <h4 class="text-center">Reconcile history</h4>
        @if(!empty($history))
            <table class="table table-striped table-bordered mt-2">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Source </th>
                    <th>Data </th>
                    <th>Done by </th>
                    <th>Date </th>
                </tr>
                </thead>
                <tbody>
                @foreach($history as $data)
                    <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{\App\Models\ReconcileStatement::$sourceList[$data['source']]}}</td>
                        <td>
                            <table class="table table-striped table-sm">
                            <tr>
                                <td>
                                    @php $metadata = json_decode($data['meta_data'], true) @endphp
                                    @foreach(['total_rows_count', 'total_settled_rows_count', 'already_settled_rows_count', 'returned_rows_count'
                                                , 'failed_rows_count', 'not_found_rows_count'] as $key)
                                        <li>{{$key}} = {{ $metadata[$key] }}</li>
                                    @endforeach
                                </td>
                                <td>
                                    @php $metadata = json_decode($data['meta_data'], true) @endphp
                                    @foreach(['file_amount', 'file_settleable_amount',
                                                'already_settled_amount', 'failed_amount', 'not_found_amount'] as $key)
                                        <li>{{$key}} = {{ $metadata[$key] }}</li>
                                    @endforeach
                                </td>
                            </tr>
                            </table>
                        </td>
                        <td>{{\App\User::find($data['imported_by'])->name}}</td>
                        <td>{{ date("d-M-y H:i:s", $data['imported_at']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="row pull-right mr-4">
                {!! $history->appends(request()->query())->render() !!}
            </div>
        @endif
    </div>

<script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
<script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
<script type="application/javascript">
    function form_submit() {
        var loader = Ladda.create(document.querySelector('#file-upload-btn')).start();
        loader.start();
    }
    function select_load(event)
    {
        window.location = "?year=" + event.value;
        return false;
    }
</script>
@endsection
