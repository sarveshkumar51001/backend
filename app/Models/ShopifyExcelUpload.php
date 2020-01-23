<?php
namespace App\Models;

class ShopifyExcelUpload extends Base
{

    protected $connection = 'mongodb';

    protected $collection = 'shopify_excel_uploads';

    protected $guarded = [];

    const DATE_FORMAT = "d/m/Y";

    const SCHOOL_TITLE = "Apeejay";

    const INTERNAL_ORDER = "internal";

    const EXTERNAL_ORDER = "external";

    const DATE_REGEX = '/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/';

    const NUM_EXPONENTIAL_REGEX = '/^[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)$/';

    const TYPE_INSTALLMENT = 'installment';

    const TYPE_ONETIME = 'one_time';

    const JOB_STATUS_PENDING = 'pending';

    const JOB_STATUS_COMPLETED = 'completed';

    const JOB_STATUS_FAILED = 'failed';

    const REYNOTT = 'Reynott';

    const MODE_CASH = 1;

    const MODE_CHEQUE = 2;

    const MODE_DD = 3;

    const MODE_PDC = 4;

    const MODE_ONLINE = 5;

    const MODE_PAYTM = 6;

    const MODE_NEFT = 7;

    const CHEQUE_DD_FIELDS = [
        'chequedd_no',
        'drawee_account_number',
        'micr_code',
        'chequedd_date',
        'drawee_name',
        'bank_name',
        'bank_branch'
    ];

    const ONLINE_FIELDS = [
        'txn_reference_number_only_in_case_of_paytm_or_online'
    ];

    const PAYMENT_METAFIELDS = [
        'installment',
        'processed',
        'errors',
        'upload_date'
    ];

    const METADATA_FIELDS = [
        'file_id',
        'job_status',
        'order_id',
        'customer_id',
        'upload_date',
        'paid',
        'pdc_collected',
        'pdc_to_be_collected'
    ];

    public static $modesTitle = [
        self::MODE_CASH => 'Cash',
        self::MODE_CHEQUE => 'Cheque',
        self::MODE_DD => 'DD',
        self::MODE_PDC => 'PDC Cheque',
        self::MODE_ONLINE => 'Online',
        self::MODE_PAYTM => 'Paytm QR Code',
        self::MODE_NEFT => 'NEFT'
    ];

    public static function payment_modes()
    {
        return array_values(self::$modesTitle);
    }

    const SCHOOL_ADDRESS_MAPPING = [
        "Apeejay" => [
            "Sheikh Sarai" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017",
                "is_higher_education" => false
            ],
            "Sheikh Sarai International" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017",
                "is_higher_education" => false
            ],
            "Pitampura" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110034",
                "is_higher_education" => false
            ],
            "Saket" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017",
                "is_higher_education" => false
            ],
            "Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201301",
                "is_higher_education" => false
            ],
            "Nerul" => [
                "city" => "Mumbai",
                "state" => "Maharashtra",
                "pincode" => "400706",
                "is_higher_education" => false
            ],
            "Kharghar" => [
                "city" => "Mumbai",
                "state" => "Maharashtra",
                "pincode" => "410210",
                "is_higher_education" => false
            ],
            "Faridabad 15" => [
                "city" => "Faridabad",
                "state" => "Haryana",
                "pincode" => "121007",
                "is_higher_education" => false
            ],
            "Faridabad 21D" => [
                "city" => "Faridabad",
                "state" => "Haryana",
                "pincode" => "121012",
                "is_higher_education" => false
            ],
            "Charkhi Dadri" => [
                "city" => "Charkhi Dadri",
                "state" => "Haryana",
                "pincode" => "127306",
                "is_higher_education" => false
            ],
            "Mahavir Marg" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001",
                "is_higher_education" => false
            ],
            "Rama Mandi" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144023",
                "is_higher_education" => false
            ],
            "Tanda Road" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001",
                "is_higher_education" => false
            ],
            "Model Town" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144003",
                "is_higher_education" => false
            ],
            "Greater Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201306",
                "is_higher_education" => false
            ],
            "Greater Kailash" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110048",
                "is_higher_education" => false
            ],
            "ACFA Mahavir Marg" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001",
                "is_higher_education" => true
            ],
            "AIMTC Rama Mandi" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144023",
                "is_higher_education" => true
            ],
            "AID New Delhi" => [
                "city" => "New Delhi",
                "state" => "New Delhi",
                "pincode" => "110062",
                "is_higher_education" => true
            ],
            "AIMC Dwarka" => [
                "city" => "New Delhi",
                "state" => "New Delhi",
                "pincode" => "110077",
                "is_higher_education" => true
            ],
            "ASM Dwarka" => [
                "city" => "New Delhi",
                "state" => "New Delhi",
                "pincode" => "110077",
                "is_higher_education" => true
            ],
            "AITCS Greater Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201308",
                "is_higher_education" => true
            ],
            "AITSM Greater Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201308",
                "is_higher_education" => true
            ],
            "AITSAP Greater Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201308",
                "is_higher_education" => true
            ],
            "SPGC Charkhi Dadri" => [
                "city" => "Charkhi Dadri",
                "state" => "Haryana",
                "pincode" => "127306",
                "is_higher_education" => true
            ]
        ],
        "Reynott" => [
            "Reynott Academy Jalandhar" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144003",
                "is_higher_education" => false
            ]
        ]
    ];

    /**
     * Function returns school/institute location based on the delivery institution and branch provided. If the
     * delivery institution exists in the school address mapping then fetch the location corresponding to the branch
     * if exists, else return false.
     *
     * @param string $delivery_institution
     * @param string $branch
     * @return array|boolean
     */
    public static function getLocation($delivery_institution, $branch)
    {
        // Checking for group
        if (array_key_exists($delivery_institution, self::SCHOOL_ADDRESS_MAPPING)) {
            $locations = self::SCHOOL_ADDRESS_MAPPING[$delivery_institution];

            // Checking for locations
            if (array_key_exists($branch, $locations)) {
                return $locations[$branch];
            }
        }
        return false;
    }

    /**
     * Returns array of school branches from school address mapping
     *
     * @return array
     */
    public static function getBranchNames() {
        return array_merge(array_keys(self::SCHOOL_ADDRESS_MAPPING["Apeejay"]),array_keys(self::SCHOOL_ADDRESS_MAPPING['Reynott']));
    }

}


