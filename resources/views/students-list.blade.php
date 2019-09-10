@extends('admin.app')

@section('content')
@inject('Student','App\Models\Student')
@inject('Settings','App\Models\Settings')
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
                @foreach ($Student::SCHOOL_LIST as $school)
                    <option value="{{ $school }}" @if($school == old('school-name')) selected @endif> {{ $school }}</option>
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
                    @foreach ($Student::CLASS_LIST as $class)
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
                                @for ($section = 'A'; $section <= 'J'; $section++)
                                    <option value="{{ $section }}" @if($section == old('section')) selected @endif> {{ $section }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                <div class="col-sm-4">
                    <label><i aria-hidden="true"></i>Session</label>
                    <div class="input-group">
                        <select name="session" class="form-control">
                            <option selected="selected" value="">Select Session </option>
                            @foreach($Settings::get_session_values() as $session)
                                <option value="{{$session}}" @if($session == old('session')) selected @endif> {{ $session }}</option>
                                @endforeach
                        </select>
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
                            <th>Session</th>
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
                            <td>{{ $student->{$Student::SESSION} }}</td>
                            <td>{{ $student->{$Student::STUDENT_FIRST_NAME} }} <button type="button" class="btn btn-default btn-copy js-tooltip js-copy" data-toggle="tooltip" data-placement="bottom" data-copy="{{$student->{$Student::STUDENT_FIRST_NAME} }}" title="Copy to clipboard"><svg class="icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="12" height="20" viewBox="0 0 20 20"><path d="M17,9H7V7H17M17,13H7V11H17M14,17H7V15H14M12,3A1,1 0 0,1 13,4A1,1 0 0,1 12,5A1,1 0 0,1 11,4A1,1 0 0,1 12,3M19,3H14.82C14.4,1.84 13.3,1 12,1C10.7,1 9.6,1.84 9.18,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3Z" /></svg></button></td>
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

@section('footer-js')
    <script src="{{ URL::asset('js/admin/clipboard_copy.js') }}"></script>
@endsection
