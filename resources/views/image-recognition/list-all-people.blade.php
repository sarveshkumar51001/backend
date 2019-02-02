@extends('admin.app')
@section('content')

<div class = "card">
  <div class="card-header">
            <i class="fa fa-edit"></i> Peoples
  </div>

    <div class="card-body">
       <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.list-all-people-result')}}" id="add-convention-form" >
  	@csrf
    <div class="card-body" >
       <div>     
          <label for="Tag" id="" class="">Tag : </label>
          <select name="tag">
            <option selected="selected"> Select Tag </option> 
            <option value="employee"> Employee </option> 
            <option value="alumni"> Alumni </option>
        </select required> 
        </div>  
          
        <div>
          <label for="Organization" id="" class="">Organization : </label>
          <select name="organization">
            <option selected="selected"> Select Organization </option> 
            <option value="valedra"> Valedra </option> 
            <option value="apeejay education society"> Apeejay Education Society </option>
            <option value="apeejay school, sheikh sarai"> Apeejay School, Sheikh Sarai </option> 
            <option value="apeejay school, saket"> Apeejay School, Saket </option>            
        </select required> 
        </div>
        <div>    
          <button id="search-btn" type="submit" class="btn btn-sm btn-primary"><i class="icon-plus"></i> &nbsp; Show </button>    
        </div>  
    </div>
  </form>
  
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
        @foreach($peoples as $people)
        	<img src="{{ $people }}" width = "150">
        @endforeach
		</div>
	</div>
@endif 
    
@endsection