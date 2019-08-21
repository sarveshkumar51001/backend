<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                'class' => 'required|numeric',
                'section' => 'string|max:1'
            ];
        
	    Validator::make($request->all(), $rules)->validate();

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
        
	    Validator::make($request->all(), $rules)->validate();

		$school_enrollment_no = $request['school-enrollment-no'];

		$students = Student::where(Student::ENROLLMENT_NO,strtoupper($school_enrollment_no))->get();

		return view('students-list')->with('students',$students)->with(['breadcrumb' => $breadcrumb]);
	}

}