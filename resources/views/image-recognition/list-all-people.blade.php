@extends('admin.app')
@section('content')
<div class = "card">
    <div class="card-header">
      <i class="fa fa-user"></i> List Key Peoples
    </div>

    @php  extract(request()->all()); @endphp

    <div class="card-body">
        <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.list-all-people-result')}}" id="add-convention-form" >
            @csrf
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="col-form-label" for="inputSuccess1">Organization</label>
                    <select name="organization" class="form-control" required>
                        <option selected="selected"> Select Organization </option>
                        <option value="valedra" @if(!empty($organization) && $organization == 'valedra') selected @endif> Valedra </option>
                        <option value="apeejay education society" @if(!empty($organization) && $organization == 'apeejay education society') selected @endif> Apeejay Education Society </option>
                        <option value="apeejay school, sheikh sarai" @if(!empty($organization) && $organization == 'apeejay school, sheikh sarai') selected @endif> Apeejay School, Sheikh Sarai </option>
                        <option value="apeejay school, saket" @if(!empty($organization) && $organization == 'apeejay school, saket') selected @endif> Apeejay School, Saket </option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="col-form-label" for="inputSuccess1">Type</label>
                    <select name="tag" class="form-control" required>
                        <option selected="selected"> Select Type </option>
                        <option value="employee" @if(!empty($tag) && $tag == 'employee') selected @endif> Employee </option>
                        <option value="alumni" @if(!empty($tag) && $tag == 'alumni') selected @endif> Alumni </option>
                    </select>
                </div>
                <div class="col-sm-3 pull-right">
                    <label>&nbsp;</label>
                    <div class="input-group">
                        <button id="search-btn" type="submit" class="btn btn-lg btn-success"> &nbsp; Search </button>
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
            <h4>Search Result:</h4>
            <div class="row">
                @foreach($peoples as $people)
                    <div class="col-md-3 mb-4">
                        <div class="col-md-5">
                            <img src="{{ $people['avatar'] }}" class="img-avatar" width="125">
                        </div>
                        <div class="col-md-7">
                            <div><strong>#{{ $loop->index+1 }}</strong> &nbsp;{{ $people['name'] }}</div>
                            <div class="small text-muted">
                                <span>Tags: </span>
                                @foreach($people['tags'] as $tag)
                                    <span class="badge">{{ $tag }}</span> @if(!$loop->last)|@endif
                                @endforeach
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
		</div>
	</div>
@endif 
    
@endsection