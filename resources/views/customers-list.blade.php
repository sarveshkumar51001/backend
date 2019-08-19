@extends('admin.app')

@section('content')
@inject('Customer','App\Models\Customer')

    <div class="card">
        <div class="card-header">
            <i class="icon-user"></i>Student Search
        </div>
        <div class="card-body">
            @foreach($errors->all() as $key => $value)
                <div class="alert alert-danger">
                    {{ $value }}
                </div>
            @endforeach
            <form method="POST" action="{{ route('search.student') }}" enctype="multipart/form-data" onsubmit="form_submit()">

            <div class="row">
            <div class="col-sm-4">
                <label><i class="fa fa-university" aria-hidden="true"></i> School</label>
            <div class="input-group">
                <select name="school-name" class="form-control" required="required">
                <option disabled="disabled" selected="selected">Select School </option>
                @foreach ($Customer::SCHOOL_LIST as $school)
                    <option value="{{ $school }}"> {{ $school }}</option>
                @endforeach
                </select>
            </div>
            </div>
                <div class="col-sm-4">
                <label><i class="fa fa-child" aria-hidden="true"></i> Student Name</label>
                <div class="input-group">
                    <input autocomplete="off" type="text" name="student-name" required="required" class="form-control">
                </div>
            </div>
            <div class="col-sm-4">
                <label><i class="fas fa-school" aria-hidden="true"></i> Class</label>
                <div class="input-group">
                    <select name="class" class="form-control" required="required">
                        <option disabled="disabled" selected="selected">Select Class </option>
                    @for ($class =1; $class <= 12; $class++)
                        <option value="{{ $class }}"> {{ $class }}</option>
                    @endfor
                </select>
                </div>
            </div>
        </div>
            <div class="row">
                <div class="col-sm-4">
            <label><i aria-hidden="true"></i> Section</label>
                    <div class="input-group">
                            <select name="section" class="form-control" required="required">
                            <option disabled="disabled" selected="selected">Select Section </option>
                                @for ($section = 'A'; $section <= 'H'; $section++)
                                    <option value="{{ $section }}"> {{ $section }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    {{ csrf_field() }}
                    <div class="col-sm-4">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <button id="file-upload-btn" type="submit" class="btn btn-group-sm btn-info"><i class="fa fa-search"></i> &nbsp; Search</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($students) && $students->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <i class="icon-list"></i>Results
                </div>
            <div class="card-body">
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
                    <td>{{ $student->{$Customer::STUDENT_FIRST_NAME} }}</td>
                    <td>{{ $student->{$Customer::STUDENT_LAST_NAME} }}</td>
                    <td>{{ $student->{$Customer::STUDENT_CLASS} }}</td>
                    <td>{{ $student->{$Customer::SECTION} }}</td>
                    <td>{{ $student->{$Customer::SCHOOL} }}</td>
                    <td>{{ $student->{$Customer::LOCATION} }}</td>
                    <td>{{ $student->{$Customer::ENROLLMENT_NO} }}</td>
                    <td>{{ $student->{$Customer::PARENT_FIRST_NAME} }}</td>
                    <td>{{ $student->{$Customer::PARENT_LAST_NAME} }}</td>
                    <td>{{ $student->{$Customer::EMAIL} }}</td>
                    <td>{{ $student->{$Customer::PHONE} }}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>
    </div>
    @elseif(isset($students) && $students->isEmpty())
    <h4 align="center">No data was found for the given details</h4>
    @endif
@endsection