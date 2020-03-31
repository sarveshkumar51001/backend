@extends('admin.app')

@section('content')

  <div class="card">
      <div class="card-header">
          <i class="fa fa-file"></i>Reports
      </div>
      <form method="post" action="{{route('revenue.reports')}}" enctype="multipart/form-data" id="report-form">
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
                        <label><i class="fa fa-tag">Report Type</i></label>
                        <div class="input-group">
                            <select name="report-type" class="form-control" required="required">
                                <option value="" selected disabled>Select Report Type </option>
                                @foreach(\App\Library\Shopify\Report::REPORT_MAPPING as $key => $value)
                                    <option value="{{ $key }}" @if($key == old('report-type')) selected = "selected" @endif> {{ $value['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-university" aria-hidden="true"></i> School</label>
                        <div class="input-group">
                            <select name="school-name" class="form-control" required="required">
                                <option value="" selected disabled>Select School </option>
                                <option value="-1" @if("-1" == old('school-name')) selected="selected" @endif>All Schools</option>
                                @foreach (array_keys(\App\Models\ShopifyExcelUpload::SCHOOL_ADDRESS_MAPPING["Apeejay"]) as $school)
                                    <option value="{{"Apeejay"." ".$school }}" @if("Apeejay ".$school == old('school-name')) selected="selected" @endif> Apeejay {{ $school }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label><i class="fa fa-calendar"> Period</i></label>
                        <input id="txn_range" name="daterange" class="form-control date-picker" type="text" value="{{ request('daterange') }}">
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    </div>
                </div>
                <div class="row col-lg mt-3">
                    <button type="submit" class="btn btn-lg btn-primary mr-3" name="view">View</button>
                    <button title="Download CSV" form="report-form" type="submit" class="btn btn-lg btn-success mr-3" name="download-csv" value="download-csv"><i class="fa fa-2x fa-file-excel-o"></i></button>
                </div>
            </div>
          {{ csrf_field() }}
      </form>
  </div>
  @if(!empty($data))
  <table class="table table-bordered table-striped table-sm datatable">
      <thead>
      <tr>
          @foreach(array_keys(head($data)) as $index => $key)
          <th>{{$key}}</th>
          @endforeach
      </tr>
      </thead>
      <tbody>
          @foreach($data as $doc)
              <tr>
              @foreach(\App\Library\Shopify\Report::REPORT_MAPPING[$type]['keys'] as $key)
              <td>{{$doc[$key]}}</td>
                  @endforeach
              </tr>
              @endforeach
      </tbody>
  </table>
      @elseif(empty($data) && $param == 'POST')
      <h3 style="color: red"><b>No data found for the given period.</b></h3>
    @endif
@endsection
