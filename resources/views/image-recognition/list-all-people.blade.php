@extends('admin.app')
@section('content')

<div class = "card">
    <div class="card-header">
      <i class="fa fa-user"></i> List Key Peoples
    </div>

    <div class="card-body">
        <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.list-all-people-result')}}" id="add-convention-form" >
            @csrf
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="col-form-label" for="inputSuccess1">Organization</label>
                    <select name="organization" class="form-control" required>
                        <option selected="selected"> Select Organization </option>
                        <option value="valedra"> Valedra </option>
                        <option value="apeejay education society"> Apeejay Education Society </option>
                        <option value="apeejay school, sheikh sarai"> Apeejay School, Sheikh Sarai </option>
                        <option value="apeejay school, saket"> Apeejay School, Saket </option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="col-form-label" for="inputSuccess1">Tag</label>
                    <select name="tag" class="form-control" required>
                        <option selected="selected"> Select Tag </option>
                        <option value="employee"> Employee </option>
                        <option value="alumni"> Alumni </option>
                    </select>
                </div>
                <div class="col-sm-3 pull-right">
                    <label>&nbsp;</label>
                    <div class="input-group">
                        <button id="search-btn" type="submit" class="btn btn-lg btn-success"> &nbsp; Show </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@if(!empty($error))
      <div class = "card">
    	<div class="card-body">
    	 {{ $error }}
    	</div>
	</div>
  @endif
  
@if(!empty($peoples))
    <div class = "card">
    	<div class="card-body">
            <div class="alert alert-success" role="alert">Search result:</div>
            @foreach($peoples as $people)
                <div class="row col-md-12">
                    <strong>#{{ $loop->index+1 }}</strong>
                    <div class="col-md-4">
                        <img src="{{ $people['avatar'] }}" class="img-avatar" width="125">
                    </div>
                    <div class="col-md-6">
                        Name: {{ $people['name'] }}
                        @foreach($people['tags'] as $tag)
                            <span class="badge">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
		</div>
	</div>
@endif 
    
@endsection