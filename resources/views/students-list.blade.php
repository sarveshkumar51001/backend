@extends('admin.app')

@section('content')
@inject('Student','App\Models\Student')
    @if(!(request()->has('school-enrollment-no') || request()->has('school-name')))
    <div class="card">
        <div class="card-header">
            <i class="icon-user"></i>Student Search
        </div>
        <div class="p-4">
        <div class="card mb-2">
            <div class="card-header">
            <i class="icon-user"></i>Search by Student Enrollment No
            </div>
            <form method="POST" action="{{ route('search.student-enrollment-no') }}" enctype="multipart/form-data" onsubmit="form_submit()">
                <div class = "card-body">
                	@if (!$errors->studentEnrollmentErrors->isEmpty())
                		<div class="alert alert-danger" role="alert">
                        @foreach($errors->studentEnrollmentErrors->all() as $error)
                            <p class="m-0">{{ $error }}</p>
                        @endforeach
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-sm-4">
                            <label><i class="fa fa-child" aria-hidden="true"></i> School Enrollment No.*</label>
                            <div class="input-group">
                            <input autocomplete="off" type="text" name="school-enrollment-no" required="required" class="form-control" value="{{ old('school-enrollment-no') }}">
                            </div>
                        </div>
                        {{ csrf_field() }}
                        <div class="col-sm-4">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-search"></i> &nbsp; Search</button>
                        </div>
                    </div>
                    </div>
                </div>
            </form>
        </div>
        <h3 align="center" class="text-dark">OR</h3>
        <div class="card">
            <div class="card-header">
                <i class="icon-user"></i>Search by Student Details
            </div>
            <form method="POST" action="{{ route('search.student-details') }}" enctype="multipart/form-data" onsubmit="form_submit()">
                <div class = "card-body">
                	@if (!$errors->studentDetailErrors->isEmpty())
                		<div class="alert alert-danger" role="alert">
                    	@foreach($errors->studentDetailErrors->all() as $error)
               				<p class="m-0">{{ $error }}</p>
                    	@endforeach
                    	</div>
                	@endif
            <div class="row">
            <div class="col-sm-4">
                <label><i class="fa fa-university" aria-hidden="true"></i> School*</label>
            <div class="input-group">
                <select name="school-name" class="form-control" required="required">
                <option selected="selected" value="">Select School </option>
                @foreach (App\Models\ShopifyExcelUpload::getBranchNames() as $school)
                    <option value="{{ $school }}" @if($school == old('school-name')) selected @endif> Apeejay {{ $school }}</option>
                @endforeach
                </select>
            </div>
            </div>
                <div class="col-sm-4">
                <label><i class="fa fa-child" aria-hidden="true"></i> Student Name*</label>
                <div class="input-group">
                    <input autocomplete="off" type="text" name="student-name" required="required" class="form-control" value="{{ old('student-name') }}">
                </div>
            </div>
            <div class="col-sm-4">
                <label><i class="fas fa-school" aria-hidden="true"></i> Class*</label>
                <div class="input-group">
                    <select name="class" class="form-control" required="required">
                        <option selected="selected" value="">Select Class </option>
                        @php $class_list = array_unique(array_merge(App\Models\Student::CLASS_LIST,App\Models\Student::HIGHER_CLASS_LIST,App\Models\Student::REYNOTT_CLASS_LIST,App\Models\Student::REYNOTT_DROPPER_CLASS_LIST,App\Models\Student::HAYDEN_REYNOTT_CLASS_LIST))@endphp
                    @foreach ($class_list as $class)
                        <option value="{{ $class }}" @if($class == old('class')) selected @endif> {{ $class }}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>
            <div class="row mt-2">
                <div class="col-sm-4">
            <label><i aria-hidden="true"></i> Section</label>
                    <div class="input-group">
                            <select name="section" class="form-control">
                            <option selected="selected" value="">Select Section </option>
                                @php $section_list = array_unique(array_merge(App\Models\Student::SECTION_LIST,App\Models\Student::HIGHER_SECTION_LIST,App\Models\Student::REYNOTT_SECTION_LIST,App\Models\Student::REYNOTT_DROPPER_SECTION_LIST,[App\Models\ShopifyExcelUpload::HAYDEN_REYNOTT]))@endphp
                                @foreach($section_list as $section)
                                    <option value="{{ $section }}" @if($section == old('section')) selected @endif> {{ $section }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{ csrf_field() }}
                    <div class="col-sm-4">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-success"><i class="fa fa-search"></i> &nbsp; Search</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
    @else
        <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Student Search Results
                    @if(isset($students) && $students->isNotEmpty())
                        <a href="{{ route('search.students') }}" class="btn btn-success pull-right"><i class="fa fa-search"></i> Search Again</a>
                    @endif
                </div>
            <div class="card-body">
                @if(isset($students) && $students->isNotEmpty())
                    <table class="table table-responsive-sm table-hover table-outline table-bordered table-striped table-sm datatable no-footer mb-0">
                    <thead>
                        <tr>
                            <th>Student First Name</th>
                            <th>Student Last Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>School</th>
                            <th>School Location</th>
                            <th>School Enrollment No</th>
                            <th>Parent First Name</th>
                            <th>Parent Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $student->{$Student::STUDENT_FIRST_NAME} }}</td>
                            <td>{{ $student->{$Student::STUDENT_LAST_NAME} }}</td>
                            <td>{{ $student->{$Student::STUDENT_CLASS} }}</td>
                            <td>{{ $student->{$Student::SECTION} }}</td>
                            <td>{{ $student->{$Student::SCHOOL} }}</td>
                            <td>{{ $student->{$Student::LOCATION} }}</td>
                            <td>{{ $student->{$Student::ENROLLMENT_NO} }}</td>
                            <td>{{ $student->{$Student::PARENT_FIRST_NAME} }}</td>
                            <td>{{ $student->{$Student::PARENT_LAST_NAME} }}</td>
                            <td>{{ $student->{$Student::EMAIL} }}</td>
                            <td>{{ $student->{$Student::PHONE} }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    </table>
                @else
                    <div class="text-center">
                        <h4 class="alert alert-danger">No data was found for the student given details</h4>
                        <a href="{{ route('search.students') }}" class="btn btn-danger"><i class="fa fa-search"></i> Search Again</a>
                    </div>
                @endif
        </div>
    </div>

    @endif
@endsection
