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
                            <input autocomplete="off" type="text" name="school-enrollment-no" required="required" class="form-control">
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
                <option disabled="disabled" selected="selected" value="">Select School </option>
                @foreach ($Student::SCHOOL_LIST as $school)
                    <option value="{{ $school }}"> {{ $school }}</option>
                @endforeach
                </select>
            </div>
            </div>
                <div class="col-sm-4">
                <label><i class="fa fa-child" aria-hidden="true"></i> Student Name*</label>
                <div class="input-group">
                    <input autocomplete="off" type="text" name="student-name" required="required" class="form-control">
                </div>
            </div>
            <div class="col-sm-4">
                <label><i class="fas fa-school" aria-hidden="true"></i> Class*</label>
                <div class="input-group">
                    <select name="class" class="form-control" required="required">
                        <option disabled="disabled" selected="selected" value="">Select Class </option>
                    @for ($class =1; $class <= 12; $class++)
                        <option value="{{ $class }}"> {{ $class }}</option>
                    @endfor
                </select>
                </div>
            </div>
        </div>
            <div class="row mt-2">
                <div class="col-sm-4">
            <label><i aria-hidden="true"></i> Section</label>
                    <div class="input-group">
                            <select name="section" class="form-control">
                            <option disabled="disabled" selected="selected">Select Section </option>
                                @for ($section = 'A'; $section <= 'J'; $section++)
                                    <option value="{{ $section }}"> {{ $section }}</option>
                                @endfor
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
                        <h4>No data was found for the given details</h4>
                        <a href="{{ route('search.students') }}" class="btn btn-danger"><i class="fa fa-search"></i> Search Again</a>
                    </div>
                @endif
        </div>
    </div>
    
    @endif
@endsection