@extends('admin.app')
@section('content')
    <div class ="body">
        <div class = "card">
            <div class="card-header">
                <i class="fa fa-money"></i> Reconcile
            </div>
            <div class="card-body">
                @foreach($errors->all() as $key => $value)
                    <div class="alert alert-danger">
                        {{ $value }}
                    </div>
                @endforeach
                <form enctype="multipart/form-data" method="post" action="{{ route('bulkupload.reconcile.preview') }}" id="reconcile-file-form" >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Statement source</label>
                                    <select class="form-control" name="source" required>
                                        <option value="">Select...</option>
                                        @foreach(\App\Library\Shopify\Reconciliation\File::$sourceTitles as $code => $title)
                                            <option value="{{$code}}">{{$title}}</option>
                                        @endforeach
                                    </select>
                                    <i id="source" class="error text-danger d-none"></i>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Select statement file </label>
                                    <input id="file" name="file" type="file" class="form-control form-control-sm" required>
                                    <i id="error-file" class="error text-danger d-none"></i>
                                </div>
                            </div>
                            <div class="col-sm-4 pull-right">
                                <label>&nbsp;</label>
                                <div class="input-group">
                                    <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-upload"></i> Upload</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ csrf_field() }}
                </form>
            </div>
        </div>
    </div>

<script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
<script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
<script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
<script type="application/javascript">
    function form_submit() {
        var loader = Ladda.create(document.querySelector('#file-upload-btn')).start();
        loader.start();
    }
</script>
@endsection
