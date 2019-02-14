@extends('admin.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> Search by image
        </div>
        <form enctype="multipart/form-data" method="post" action="{{ route('imagereco.search-by-image-result')}}" id="add-convention-form" >
            @csrf
            <div class="card-body">
                @if(!empty($url_to_file))
                    Image uploaded
                    <a href="{{$url_to_file}}" target="_blank"> Click to download image </a>
                @endif
                <div class="row">
                    <div class="form-group col-md-3">
                        <label class="col-form-label" for="inputSuccess1" >Type <i style="color: red;">*</i></label>
                        <select name="tag" class="form-control" required id="select-type">
                            <option selected="selected"> Select Type </option>
                            <option value="Ex-Employee"> Ex-Employee </option>
                            <option value="Employee"> Employee </option>
                            <option value="Alumni"> Alumni </option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="col-form-label" for="inputSuccess1">Organization <i style="color: red;">*</i></label>
                        <select name="organization" class="form-control" required>
                            <option selected="selected"> Select Organization </option>
                            <option value="Valedra"> Valedra </option>
                            <option value="Apeejay Education Society"> Apeejay Education Society </option>
                            <option value="Apeejay School, Sheikh Sarai"> Apeejay School, Sheikh Sarai </option>
                            <option value="Apeejay School, Saket"> Apeejay School, Saket </option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="col-form-label" for="inputSuccess1">Name <i style="color: red;">*</i></label>
                        <input name="name" type="text" value="" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="col-form-label" for="inputSuccess1">Upload Picture <i style="color: red;">*</i></label>
                        <input id="file" name="file" type = "file" class="form-control form-control-sm">
                        <i id="error-file" class="error text-danger d-none"></i>
                    </div>
                </div>
                </hr>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label class="col-form-label">Phone Number</label>
                        <input id="phone" name="contact" type="mumber" value="" class="form-control">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="col-form-label">Email ID</label>
                        <input name="email" type="email" id="email" pattern=".+@gmail.com" class="form-control">
                    </div>

                    <div class="form-group col-md-3 mt-4">
                        <div class="input-group">
                            <button id="search-btn" type="submit" class="btn btn-lg btn-success"> Search </button>
                        </div>
                    </div>
                </div>
                <div class="card d-none" id="alumni-data">
                    <div class="card-header">
                        Alumni details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="col-form-label" for="inputSuccess1">Passout year</label>
                                <input name="passout_year" type="text" value="" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="col-form-label">Passout Class</label>
                                <input name="passout_class" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card d-none" id="employee-data">
                    <div class="card-header">
                        Employee details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label class="col-form-label" for="inputSuccess1">Professions</label>
                                <input name="profession" type="text" value="" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="col-form-label">Designation</label>
                                <input name="designation" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="col-form-label">Current City</label>
                                <input name="city" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="col-form-label" for="inputSuccess1">Leaving Year</label>
                                <input name="leaving_year" type="text" value="" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    @if(!empty($results))
        <div class = "card">
            <div class="card-body">
                <h4>Search Result:</h4>
                @foreach($results as $result)
                    <img src="{{ $result }}" width = "100">
                @endforeach
            </div>
        </div>
    @endif
@endsection

@section('footer-js')
    <script>
        $('#select-type').on('change', function() {
           if(this.value == 'Employee' || this.value == 'Ex-Employee') {
               $('#alumni-data').addClass('d-none');
               $('#employee-data').removeClass('d-none');
           } else if(this.value == 'Alumni') {
               $('#alumni-data').removeClass('d-none');
               $('#employee-data').addClass('d-none');
           }
        });
    </script>
@endsection