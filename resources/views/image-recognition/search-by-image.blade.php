@extends('admin.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> Search by image
        </div>
        <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.search-by-image-result')}}" id="add-convention-form" >
            @csrf
            <div class="card-body">
                <div>     
                    <label for="Name" id="" class=""> Name : </label>   
                    <input id="" name="name" type="text" value="" required> 
                </div>
                <div>     
                    <label for="Contact" id="" class=""> Phone Number : </label>
                    <input id="" name="contact" type="mumber" value="" size ="10" required>
                </div>
                <div>     
                    <label for="Email" id="" class=""> Email ID : </label>
                    <input type="email" id="email" pattern=".+@gmail.com" size="50" required>
                </div>
                <div>     
                    <label for="Tag" id="" class=""> Tag : </label>
                    <select name="tag">
                        <option selected="selected"> Select Tag </option>
                        <option value="Ex-Employee"> Ex-Employee </option> 
                        <option value="Empolyee"> Employee </option> 
                        <option value="Alumni"> Alumni </option>
                    </select required>
                </div>
                <div>     
                    <label for="Organization" id="" class=""> Organization : </label>
                    <select name="organization">
                        <option selected="selected"> Select Organization </option>
                        <option value="Apeejay Education Society"> Apeejay Education Society </option> 
                        <option value="Valedra"> Valedra </option> 
                    </select required>
                </div>
                <div>     
                    <label for="Passout Year" id="" class=""> Passout Year : </label>
                    <input id="" name="passout_year" type="text" value="" > 
                </div>
                <div>     
                    <label for="Passout Class" id="" class=""> Passout Class : </label>
                    <input id="" name="passout_class" type="text" value="" > 
                </div>
                <div>     
                    <label for="Joining Year" id="" class=""> Joining Year : </label>
                    <input id="" name="joining_year" type="text" value="" > 
                </div>
                <div>     
                    <label for="Leaving Year" id="" class=""> Leaving Year : </label>
                    <input id="" name="leaving_year" type="text" value="" > 
                </div>
                <div>     
                    <label for="Profession" id="" class=""> Profession : </label>
                    <input id="" name="profession" type="text" value="" required> 
                </div>
                <div>     
                    <label for="Designation" id="" class=""> Designation : </label>
                    <input id="" name="designation" type="text" value="" > 
                </div>
                <div>     
                    <label for="City" id="" class=""> Current City : </label>
                    <input id="" name="city" type="text" value="" > 
                </div>

                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label >Upload Picture : </label>
                            <input id="file" name="file" type = "file" class="form-control form-control-sm">
                            <i id="error-file" class="error text-danger d-none"></i>
                        </div>
                    </div>
                </div>

                @if(!empty($url_to_file))
                    Image uploaded
                    <a href="{{$url_to_file}}" target="_blank"> Click to download image </a>
                @endif
                <button id="bulk-upload-btn" type="submit" class="btn btn-sm btn-primary"><i class="icon-plus"></i> &nbsp; Search </button>
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