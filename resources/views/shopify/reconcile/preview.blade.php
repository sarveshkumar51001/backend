@extends('admin.app')

@section('breadcrumb-items')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fa fa-edit"></i> Reconcile Preview
    </div>
    <div id="reconcile-upload-success" class="alert alert-success d-none" role="alert" style=""><i class="fa fa-check-circle-o"></i><strong> Reconcillation done successfully</strong>
    </div>
    <div id="reconcile-upload-error" class="alert alert-danger d-none" role="alert" style=""><strong> Oops! There is some error, Please correct it and try again</strong>
        <div id="reconcile-error">
        </div>
    </div>
    @if(empty($meta['nextstep']) && strpos(request()->url(), 'preview') === false)
        <div class="alert alert-danger" role="alert" style=""><strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                Oops! There are no records matched for reconcile, so can't process further.</strong>
            <ul><li>At least one record should match to complete the action.</li></ul>
        </div>
    @endif
    @if(!empty($error))
        <div id="reconcile-upload-error" class="alert alert-danger" role="alert" style=""><strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                Oops! There is some error, Please correct it and try again</strong>
            <ul>{{$error}}</ul>
        </div>
    @elseif(!empty($errors))
        <div id="reconcile-upload-error" class="alert alert-danger" role="alert" style=""><strong><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                Oops! There is some error, Please correct it and try again</strong>
            <ul>
                @foreach ($errors as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>

    @else
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="callout callout-info b-t-1 b-r-1 b-b-1">
                    <small class="text-muted">ROWS IN SHEET</small><br>
                    <strong class="h4">{{$metrics['total_rows_count']}}</strong>
                </div>
            </div>
            <div class="col-6">
                <div class="callout callout-success b-t-1 b-r-1 b-b-1">
                    <small class="text-muted">SHEET TOTAL</small><br>
                    <strong class="h4">₹ {{amount_inr_format($metrics['file_amount'])}}</strong>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-value-lg text-success font-weight-bold">{{ $metrics['total_settled_rows_count']/$metrics['total_rows_count'] * 100}}% ({{$metrics['total_settled_rows_count']}})</div>
                        <div class="text-muted text-uppercase font-weight-bold small">Reconcileable Transactions</div>
                        <div class="progress progress-xs my-2">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $metrics['total_settled_rows_count']/$metrics['total_rows_count'] * 100}}%" aria-valuenow="{{ $metrics['file_settleable_amount']/$metrics['file_amount'] * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div><div class="text-value-lg font-weight-bold text-success">₹ {{amount_inr_format($metrics['file_settleable_amount'])}}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-value-lg text-primary font-weight-bold">{{ $metrics['already_settled_rows_count']/$metrics['total_rows_count'] * 100}}% ({{$metrics['already_settled_rows_count']}})</div>
                        <div class="text-muted text-uppercase font-weight-bold small">Already Settled Transactions</div>
                        <div class="progress progress-xs my-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $metrics['already_settled_rows_count']/$metrics['total_rows_count'] * 100}}%" aria-valuenow="{{ $metrics['file_settleable_amount']/$metrics['file_amount'] * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div><div class="text-value-lg font-weight-bold text-primary">₹ {{amount_inr_format($metrics['already_settled_amount'])}}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-value-lg text-danger font-weight-bold">{{ $metrics['failed_rows_count']/$metrics['total_rows_count'] * 100}}% ({{$metrics['failed_rows_count']}})</div>
                        <div class="text-muted text-uppercase font-weight-bold small">Failed Transactions</div>
                        <div class="progress progress-xs my-2">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $metrics['failed_rows_count']/$metrics['total_rows_count'] * 100}}%" aria-valuenow="{{ $metrics['file_settleable_amount']/$metrics['file_amount'] * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div><div class="text-value-lg font-weight-bold text-danger">₹ {{amount_inr_format($metrics['failed_amount'])}}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-value-lg text-warning font-weight-bold">{{ $metrics['not_found_rows_count']/$metrics['total_rows_count'] * 100}}% ({{$metrics['not_found_rows_count']}})</div>
                        <div class="text-muted text-uppercase font-weight-bold small">Not Found Transactions</div>
                        <div class="progress progress-xs my-2">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $metrics['not_found_rows_count']/$metrics['total_rows_count'] * 100}}%" aria-valuenow="{{ $metrics['file_settleable_amount']/$metrics['file_amount'] * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div><div class="text-value-lg font-weight-bold text-warning">₹ {{amount_inr_format($metrics['not_found_amount'])}}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div id="reconcile-upload-button" class="col-lg-6">
                @if(!empty($meta['nextstep']) && $meta['nextstep'] == \App\Library\Shopify\Reconciliation\Reconcile::MODE_SETTLE)
                    <button data-style="contract-overlay" class="btn btn-success btn-lg float-right" id="reconcile-button" type="button" onclick='reconcile("{{$meta['source']}}", "{{ urlencode($meta['filePath']) }}", "{{$meta['checksum']}}", "{{request('from')}}", "{{request('to')}}", "{{session('c_org')}}");'>Reconcile ({{$metrics['total_settled_rows_count']}})</button>
                @else
                    <button  disabled data-style="contract-overlay" class="btn btn-success btn-lg float-right" title="No Transactions found to reconcile" id="reconcile-button" type="button" >Reconcile (0)</button>
                @endif
            </div>
        </div>

        <div class="row container-fluid">
            <table id="previewdata" class="table table-bordered table-sm no-footer datatable">
            <thead>
            <tr>
                <td colspan="12">
                <ul class="horizontal-bars">
                    <li class="legend">
                        <span class="badge badge-pill badge-info" style="background-color: #3CB371"></span>&nbsp;<small>Success</small> &nbsp;&nbsp;
                        <span class="badge badge-pill badge-info" style="background-color: #ff8396"></span>&nbsp;<small>Error</small> &nbsp;&nbsp;
                        <span class="badge badge-pill badge-info" style="background-color: #FFE4B5"></span>&nbsp;<small>Not found</small> &nbsp;&nbsp;
                    </li>
                </ul>
                </td>
            </tr>

            <tr>
                    <th>#</th>
                @foreach ($columns as $title)
                    <th>{{$title}}</th>
                @endforeach
                <th>Reco Remarks</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($previewdata as $row)
                @if($row['reco_status'] == 400)
                    <tr bgcolor="#ff8396" data-toggle="tooltip" data-placement="top" title="{{ $row['error'] }}">
                @elseif($row['reco_status'] == 404)
                    <tr bgcolor="#FFE4B5">
                @elseif($row['reco_status'] == 200 || $row['reco_status'] == 409)
                    <tr bgcolor="#20c997">
                @else
                    <tr>
                @endif
                    <td>{{$loop->index + 1}}</td>
                    @foreach ($columns as $key => $title)
                        @if(array_key_exists($key, $row))
                            <td>{{$row[$key]}}</td>
                        @else
                            <td></td>
                        @endif
                    @endforeach
                        <td>{{ $row['error'] ?? (in_array($row['reco_status'], [200, 209]) ?  'Transaction matches' : '') }}</td>
            @endforeach
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    @endif
    <a href="{{route('bulkupload.reconcile.index')}}" class="btn btn-sm btn-danger">&lt;&lt; Go Back</a>

</div>
@endsection

@section('footer-js')
    <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
    <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
    <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
    <script src="{{ URL::asset('js/shopify/reconcile.js') }}"></script>
@endsection
