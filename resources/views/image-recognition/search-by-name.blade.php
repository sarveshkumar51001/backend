@extends('admin.app')
@section('content')

<<<<<<< HEAD
<div class = "card">
  <div class="card-header">
      <i class="fa fa-edit"></i> Search by Name
  </div>
  <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.search-by-name-result')}}" id="add-convention-form" >
    @csrf
    <div class="card-body" >
      <div>     
        <label for="Type" id="" class=""> Search Type : </label>
        <select name="type">
            <option selected="selected"> Select Type </option> 
            <option value="employee"> Employee </option> 
            <option value="alumni"> Alumni </option>
            <option value="event"> Event </option>
            <option value="tag"> Tag </option>
        </select required>
      </div>

      <div>     
        <label for="Name" id="" class=""> Search Name : </label>
        <input id="" name="name" type="text" value="{{ old('name') }}"> 
      </div>  
          
      <div>
        <label for="Tag" id="" class=""> Search By Tag : </label>
        <input id="" name="tag" type="text" value="{{ old('name') }}">
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
	<img src="{{ $result }}" width = "150">
@endforeach

@endif
</div></div>
=======
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
>>>>>>> dc93c9c259c012e8cc16dd47083579b09d7063db
@endsection

