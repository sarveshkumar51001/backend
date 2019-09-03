<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends BaseController
{
	public function index() {

	    $breadcrumb = ['Students' => ''];
	    return view('students-list', ['breadcrumb' => $breadcrumb]);
    }


    public function search_by_student_details(Request $request){

    	$breadcrumb = ['Students' => route('search.students'),'Search By Student Details' => ''];
		$rules = [
                'school-name' => 'required|string',
                'student-name' => 'required|string|min:3|max:100',
	            'class' => ["required",Rule::in(Student::CLASS_LIST)],
                'section' => 'max:1'
            ];
        
		$validator = Validator::make($request->all(), $rules);
		$validator->setAttributeNames([
		    'school-name' => 'School Name',
		    'student-name' => 'Student Name',
		    'class' => 'Class',
		    'section' => 'Section'
		]); 
		
		if ($validator->fails())
		{
		    return redirect()->route('search.students')->withErrors($validator, 'studentDetailErrors')->withInput();
		}

		$school_name = $request['school-name'];
		$class = $request['class'];
		$section = $request['section'];
		$student_name = $request['student-name'];

		$students = Student::where(Student::SCHOOL,$school_name)->where(function($query) use ($student_name)
            {
                $query->where(Student::STUDENT_FIRST_NAME, 'like', "%{$student_name}%")
                      ->orWhere(Student::STUDENT_LAST_NAME, 'like', "%{$student_name}%");
            })->where(Student::STUDENT_CLASS,$class);

		if(!empty($section)){
			$students = $students->where(Student::SECTION,$section)->get();
		}
		else{
			$students = $students->get();
		}

		return view('students-list')->with('students',$students)->with(['breadcrumb' => $breadcrumb]);
	}

	public function search_by_student_enrollment_no(Request $request){

		$breadcrumb = ['Students' => route('search.students'),'Search By Student Details' => ''];

		$rules = ['school-enrollment-no' => "required|string|min:4|regex:/[a-zA-Z]+-[0-9]+/"];
        
		$validator = Validator::make($request->all(), $rules, ['school-enrollment-no.regex' => 'The Student Enrollment Number format is invalid. Should be in format SKT-XXX']);
		
		if ($validator->fails())
		{
		    return redirect()->route('search.students')->withErrors($validator, 'studentEnrollmentErrors')->withInput();
		}

		$school_enrollment_no = $request['school-enrollment-no'];

		$students = Student::where(Student::ENROLLMENT_NO,strtoupper($school_enrollment_no))->get();

		return view('students-list')->with('students',$students)->with(['breadcrumb' => $breadcrumb]);
	}

}