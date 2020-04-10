@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-file"></i>Reports
        </div>
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
                        <label><i class="fa fa-tag">Page</i></label>
                        <div class="input-group">
                            <select name="page_id" class="form-control" required="required">
                                <option value="" selected disabled>Select Page Name </option>
                                @foreach($Pages as $Page)
                                    <option value="{{ $Page['page_id'] }}" @if($Page['page_id'] == old('page_id')) selected = "selected" @endif> {{ $Page['page_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-calendar"> Period</i></label>
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
    @if(!empty($data))
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
                        <td>{{$value['data']['body'][$key]}}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="row pull-right mr-4">
            {!! $data->appends(request()->query())->render() !!}
        </div>
    @elseif(empty($data) && $param == 'get')
     <h3 style="color: red"><b>No data found for the given period.</b></h3>
    @endif
@endsection
