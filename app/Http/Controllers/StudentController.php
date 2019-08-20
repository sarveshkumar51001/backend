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


    public function search_student(Request $request){

		$rules = [
                'school-name' => 'required|string',
                'student-name' => 'required|string|min:3|max:100',
                'class' => 'required|numeric',
                'section' => 'required|string|max:1'
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
            })->where(Student::STUDENT_CLASS,$class)->where(Student::SECTION,$section)->get();

		return view('students-list')->with('students',$students);
	}
}