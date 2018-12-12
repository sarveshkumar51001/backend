@extends('admin.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <i class="fa fa-edit"></i> Search by image
        </div>
        <form enctype="multipart/form-data" method="post" action="{{url('imagereco/search')}}" id="add-convention-form" >
            <div class="card-body">
                @if(!empty($url_to_file))
                    Image uploaded
                    <a href="{{$url_to_file}}" target="_blank">Click to download image</a>
                @endif
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label >Select File </label>
                            <input id="file" name="file" type = "file" class="form-control form-control-sm">
                            <i id="error-file" class="error text-danger d-none"></i>
                        </div>
                    </div>
                </div>
                <button id="bulk-upload-btn" type="submit" class="btn btn-sm btn-primary"><i class="icon-plus"></i> &nbsp; Upload</button>
                {{ csrf_field() }}
            </div>
        </form>
    </div>

@endsection