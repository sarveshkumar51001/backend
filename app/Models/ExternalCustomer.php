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

    const CLASS_LIST = ["Nursery", "KG", 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    const HIGHER_CLASS_LIST = ["BA","MA","BOD","BCOM","BTECH","MCOM","BBA","MBA","BSC","BVOC"];

    const SECTION_LIST = ["A","B","C","D","E","F","G","H","I","J"];

    const HIGHER_SECTION_LIST = ["Sem 1","Sem 2","Sem 3","Sem 4","Sem 5","Sem 6","Sem 7","Sem 8"];

    const REYNOTT_CLASS_LIST = [7,8,9,10,11,12];

    const REYNOTT_DROPPER_CLASS_LIST = ["Dropper","Crash"];

    const REYNOTT_SECTION_LIST = ["A","B","C","D","E","F"];

    const REYNOTT_DROPPER_SECTION_LIST = ["Reynott"];

    const HAYDEN_REYNOTT_CLASS_LIST = [9,10,11,12,"Creative Arts"];

}
