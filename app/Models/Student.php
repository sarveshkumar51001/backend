<?php

namespace App\Models;

class Student extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'student_details';
	protected $guarded = [];

	const SCHOOL_LIST = [
		"Apeejay Sheikh Sarai",
		"Apeejay Sheikh Sarai International",
		"Apeejay Pitampura",
		"Apeejay Saket",
		"Apeejay Noida",
		"Apeejay Nerul",
		"Apeejay Kharghar",
		"Apeejay Faridabad 15",
		"Apeejay Faridabad 21D",
		"Apeejay Charkhi Dadri",
		"Apeejay Mahavir Marg",
		"Apeejay Rama Mandi",
		"Apeejay Tanda Road",
		"Apeejay Greater Noida",
		"Apeejay Greater Kailash",
	];

	const SCHOOL = "school_name";

	const STUDENT_CLASS = "class";

	const STUDENT_FIRST_NAME = "student_first_name";

	const STUDENT_LAST_NAME = "student_last_name";

	const SECTION = "section";

	const LOCATION = "school_location";

	const ENROLLMENT_NO = "school_enrollment_no";

	const PARENT_FIRST_NAME = "parent_first_name";

	const PARENT_LAST_NAME = "parent_last_name";

	const EMAIL = "email_id";

	const PHONE = "phone";

    const CLASS_LIST = [
        "Nursery",
        "KG",
        1,2,3,4,5,6,7,8,9,10,11,12
    ];

}
