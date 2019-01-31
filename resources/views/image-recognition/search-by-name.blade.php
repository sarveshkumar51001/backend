@extends('admin.app')
@section('content')

<div class = "card">
  <div class="card-header">
            <i class="fa fa-edit"></i> Search by Name
  </div>
  <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.search-by-name-result')}}" id="add-convention-form" >
  @csrf
    <div class="card-body" >
       <div>     
          <label for="Name" id="" class="">Search Name : </label>
          <input id="" name="name" type="text" value="{{ old('name') }}"> 
        </div>  
          
        <div>
          <label for="Organization" id="" class="">Tag : </label>
          <input id="" name="organization" type="text" value="">
        </div>
        <div>    
          <button id="search-btn" type="submit" class="btn btn-sm btn-primary"><i class="icon-plus"></i> &nbsp; Search</button>    
        </div>  
    </div>
  </form>
</div>

@if(!empty($results))
<div class = "card">
<div class="card-body" >
@foreach($results as $result)
	<img src="{{ $result }}" width = "100">
@endforeach

@endif
</div></div>
@endsection

