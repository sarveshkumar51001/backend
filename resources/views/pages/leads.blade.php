@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-file"></i>Leads Reports
        </div>
        @if(request('page_id') && empty($data->total()))
            <div class="alert alert-danger" style="text-align: center">
                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> No data found for the specified filter
            </div>
        @endif
        <form method="get" action="{{route('pages.leads')}}"  id="report-form">
            <div class = "card-body">
                @if (!$errors->Errors->isEmpty())
                    <div class="alert alert-danger" role="alert">
                        @foreach($errors->Errors->all() as $error)
                            <p class="m-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="row">
                    <div class="col-sm-4">
                        <label><i class="fa fa-tag"></i> Select Page</label>
                        <div class="input-group">
                            <select name="page_id" class="form-control select2" required="required">
                                <option value="" selected disabled>Select Page Name </option>
                                @foreach($Pages as $Page)
                                    <option value="{{ $Page['page_id'] }}" @if($Page['page_id'] == old('page_id')) selected = "selected" @endif> {{ $Page['page_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-calendar"></i> Period</label>
                        <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                    </div>
                </div>
                <div class="row col-lg mt-3">
                    <button type="submit" class="btn btn-lg btn-primary mr-3" name="view">View</button>
                    <button type="submit" title="Download CSV" form="report-form" class="btn btn-lg btn-success mr-3" name="download-csv" value="download-csv"><i class="fa fa-2x fa-file-excel-o"></i></button>
                </div>
            </div>
            {{ csrf_field() }}
        </form>
    </div>
    @if(request('page_id') && !empty($data->total()))
        <table class="table table-bordered table-striped table-sm datatable">
            <thead>
            <tr>
                @if(!empty($fields['lead_fields']))
                    @foreach($fields['lead_fields'] as $index => $key)
                        <th>{{$key}}</th>
                    @endforeach
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($data as $value)
                <tr>
                    @foreach($fields['lead_fields'] as $index => $key)
                        @if($key == 'Captured At')
                            <td>{{ date("d-M-y H:i:s", $value['created_at']) }}</td>
                        @else
                            <td>{{$value['data']['body'][$key] ?? ''}}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="row pull-right mr-4">
            {!! $data->appends(request()->query())->render() !!}
        </div>
    @endif
@endsection
