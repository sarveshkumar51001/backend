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
                        <input id="" name="tag" type="text" value="">
                    </div>

                    <div>
                        <label for="Organization" id="" class="">Organization : </label>
                        <input id="" name="organization" type="text" value="">
                    </div>
                    <div>
                        <button id="search-btn" type="submit" class="btn btn-sm btn-primary"><i class="icon-plus"></i> &nbsp; Search</button>
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
                            <img src="{{ $people }}" width = "100">
                        @endforeach
                    </div>
                </div>
    @endif



@endsection