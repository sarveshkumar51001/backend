@extends('admin.app')
@section('content')

<div class = "card">
  <div class="card-header">
      <i class="fa fa-edit"></i> Search by Name
  </div>
  <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.search-by-name-result')}}" id="add-convention-form" >
    @csrf
    <div class="card-body">
        <div class="row">
            <div class="form-group col-md-2">
                <label class="col-form-label" for="inputSuccess1">Type <i style="color: red;">*</i></label>
                <select name="type" class="form-control" required>
                    <option value=""> Select... </option>
                    <option value="employee" @if(request('type') == 'employee') selected @endif> Employee </option>
                    <option value="alumni" @if(request('type') == 'alumni') selected @endif> Alumni </option>
                    <option value="event" @if(request('type') == 'event') selected @endif> Event </option>
                    <option value="tag" @if(request('type') == 'tag') selected @endif> Tag </option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label class="col-form-label" for="inputSuccess1">Organization <i style="color: red;">*</i></label>
                <select name="organization" class="form-control" required >
                    <option selected="selected"> Select Organization </option>
                    <option value="Valedra" @if(request('organization') == 'Valedra') selected @endif> Valedra </option>
                    <option value="apeejay education society" @if(request('organization') == 'apeejay education society') selected @endif> Apeejay Education Society </option>
                    <option value="apeejay school, sheikh sarai" @if(request('organization') == 'apeejay school, sheikh sarai') selected @endif> Apeejay School, Sheikh Sarai </option>
                    <option value="apeejay school, saket" @if(request('organization') == 'apeejay school, saket') selected @endif> Apeejay School, Saket </option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label" for="inputSuccess1">Search by Name <i style="color: red;">*</i></label>
                <input id="" name="name" type="text" value="{{ request('name') ?? '' }}" class="form-control">
            </div>
            <div class="form-group col-md-3">
                <label class="col-form-label" for="inputSuccess1">Search by Keywords <i style="color: red;">*</i></label>
                <input id="" name="tag" type="text" value="{{ request('tag') ?? '' }}" class="form-control">
            </div>
            <div class="col-sm-2 pull-right">
                <label>&nbsp;</label>
                <div class="input-group">
                    <button id="search-btn" type="submit" class="btn btn-lg btn-success"> &nbsp; Search </button>
                </div>
            </div>
        </div>
      <div>
  </form>
</div>

@if(!empty($results))
<div class = "card">
<div class="card-body" >
    <h4>Search Result:</h4>
@foreach($results as $result)
	<img src="{{ $result }}" width = "150">
@endforeach

@endif
</div></div>
@endsection

