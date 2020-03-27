@extends('admin.app')

@section('content')

  <div class="card">
      <div class="card-header">
          <i class="fa fa-file"></i>Reports
      </div>
      <form method="get" action="" enctype="multipart/form-data" id="report-form">
            <div class = "card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <label><i class="fa fa-tag">Report Type</i></label>
                        <div class="input-group">
                            <select name="report-type" class="form-control" required="required">
                                <option selected="selected" value="">Select Report Type </option>
                                @foreach(\App\Library\Shopify\Report::REPORT_NAME_MAPPING as $key => $value)
                                <option value="{{ $key }}" @if($key == old('report-type')) selected @endif> {{ $value }}</option>
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
              @foreach(\App\Models\ShopifyExcelUpload::CHEQUE_REPORT_KEYS as $key)
              <td>{{$doc[$key]}}</td>
                  @endforeach
              </tr>
              @endforeach
      </tbody>
  </table>
      @elseif(empty($data) && $param)
      <h3 style="color: red"><b>No data found for the given period.</b></h3>
    @endif
@endsection
