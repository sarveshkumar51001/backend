@extends('admin.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="get" action="" class="form-group">
                <div class="row ml-3 mr-4">
                    <div class="col-sm-4">
                        <label>Search query</label>
                        <div class="input-group">
                            <input autocomplete="off" id="qry" name="qry" maxlength="50" type="text" required="required" class="form-control" value="{{ request('qry') }}" placeholder="Search by Name, ID, Acc.No., Enroll No....">
                        </div>
                    </div>
                    @if(in_array(\Auth::user()->email, \App\Http\Controllers\ShopifyController::$adminTeam))
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>School</label>
                                <select name="school-name" class="form-control">
                                    <option selected="selected" value="">Select School </option>
                                    @foreach (App\Models\ShopifyExcelUpload::getBranchNames() as $school)
                                        <option value="{{ 'Apeejay '.$school }}" @if($school == old('school-name')) selected @endif> Apeejay {{ $school }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="col-sm-4">
                        <label>Date</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input autocomplete="off" id="date" name="date" maxlength="50" type="text" class="form-control datepicker" value="{{ request('date') }}" placeholder="(Cheque / Enrollment / Upload) Date">
                        </div>
                    </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Transaction Mode</label>
                                <select name="mode" class="form-control">
                                    <option selected="selected" value="">Select Mode </option>
                                    @foreach (App\Models\ShopifyExcelUpload::payment_modes() as $mode)
                                        <option value="{{ $mode }}" @if($mode == old('mode')) selected @endif>{{ $mode }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    <div class="col-sm-4 pull-right">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <button  type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-search"></i> Search</button>
                            &nbsp;&nbsp;<a href="{{route('bulkupload.search')}}"><button type="button" class="btn btn-group-sm btn-danger"> Clear</button></a>
                        </div>
                    </div>
                </div>
            </form>
            @if(!empty($result['students']->items())|| !empty($result['orders']->items()))
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-search"></i>  Search result of <strong>"{{ $query ?? '' }}"</strong>
                            <ul class="nav nav-tabs float-right" role="tablist">
                                <li class="nav-item">
                                    <a tab="" class="nav-link @if(count($result['students'])) active @endif" data-toggle="tab" href="#students" role="tab">Students
                                        <span class="badge badge-pill badge-success">{{ count($result['students']) }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a tab="" class="nav-link @if(count($result['orders']) && !count($result['students'])) active @endif" data-toggle="tab" href="#orders" role="tab">Orders
                                        <span class="badge badge-pill badge-success">{{ count($result['orders']) }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content">
                                <div class="tab-pane @if(count($result['students'])) active  @endif" id="students">
                                    <table class="table table-bordered table-striped table-sm datatable">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Organization</th>
                                            <th>Program</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['students']))
                                            @foreach($result['students'] as $student)
                                                <tr>
                                                    <td>{{$loop->index + 1}}</td>
                                                    <td>{{ $student->school_enrollment_no }}</td>
                                                    <td>{{ $student->student_first_name }}</td>
                                                    <td>{{ $student->email_id }} | {{ $student->phone }}</td>
                                                    <td>{{ $student->school_name }}</td>
                                                    <td>{{ $student->class.$student->section }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="18">No record found.</td></tr>
                                        @endif
                                        </tbody>
                                    </table>
                                    <div class="row pull-right mr-4">
                                        {!! $result['students']->render() !!}
                                    </div>
                                </div>
                                <div class="tab-pane @if(count($result['orders'])) active @endif" id="orders">
                                    <table class="table table-bordered table-striped table-sm datatable">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Parent Name</th>
                                            <th>Student Name</th>
                                            <th>Activity Name</th>
                                            <th>Activity Fee</th>
                                            <th>Upload Date</th>
                                            <th>Order Type</th>
                                            <th>Uploaded By</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['orders']))
                                            @foreach($result['orders'] as $order)
                                                <tr>
                                                    <td>@if(!empty($order->shopify_order_name))
                                                        <a target="_blank" href="https://{{ env('SHOPIFY_STORE') }}/admin/orders/{{$order->order_id}}" title="View Order on Shopify">View {{ $order->shopify_order_name }} <i class="fa fa-external-link"></i></a>
                                                    @else
                                                        <a target="_blank" href="https://{{ env('SHOPIFY_STORE') }}/admin/orders/{{$order->order_id}}" title="View Order on Shopify">View <i class="fa fa-external-link"></i></a>
                                                        @endif</td>
                                                    <td>{{ $order->parent_first_name }}</td>
                                                    <td>{{ $order->student_first_name }}</td>
                                                    <td>{{ $order->activity }}</td>
                                                    <td>{{ $order->activity_fee }}</td>
                                                    <td>{{ $order->upload_date }}</td>
                                                    <td>{{ $order->order_type }}</td>
                                                    <td>{{\App\User::where('_id',$order->uploaded_by)->first(['name'])['name']}}</td>
                                                    <td>{{$order->job_status}}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                    <div class="row pull-right mr-4">
                                        {!! $result['orders']->render() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
@endsection

            @section('footer-js')
                <script src="{{ URL::asset('vendors/js/spin.min.js') }}"></script>
                <script src="{{ URL::asset('vendors/js/ladda.min.js') }}"></script>
                <script src="{{ URL::asset('js/views/loading-buttons.js') }}"></script>
                <script src="{{ URL::asset('vendors/js/jquery-ui.min.js') }}"></script>
                <script src="{{ URL::asset('js/views/datepicker.js') }}"></script>
                <script src="{{ URL::asset('js/admin/custom.js') }}"></script>
                <script>
                    // Load date picker
                    $('.datepicker').datepicker({format: 'dd/mm/yyyy'}).on('changeDate', function(ev) {
                        $(this).datepicker('hide');
                    });
                    function form_submit() {
                        var loader = Ladda.create(document.querySelector('#file-upload-btn')).start();
                        loader.start();
                    }
                </script>
@endsection
