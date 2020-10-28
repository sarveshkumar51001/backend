<?php
namespace App\Models;

class ExternalCustomer extends Base
{
    protected $connection = 'mongodb';
    protected $collection = 'external_customers';
    protected $guarded = [];

    const EMAIL = "email_id";
    const PHONE = "phone";
    const PARENT_FIRST_NAME = "parent_first_name";

    const PARENT_LAST_NAME = "parent_last_name";
    const SOURCE_CODE = "source_code";
    const ENROLLMENT_NO = "school_enrollment_no";
    const STUDENT_FIRST_NAME = "student_first_name";
    const STUDENT_LAST_NAME = "student_last_name";
    const STUDENT_CLASS = "class";
    const SECTION = "section";
    const SCHOOL = "school_name";
    const LOCATION = "school_location";
}
