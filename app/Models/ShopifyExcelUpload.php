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
                "pincode" => "110017"
            ],
            "Sheikh Sarai International" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017"
            ],
            "Pitampura" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110034"
            ],
            "Saket" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017"
            ],
            "Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201301"
            ],
            "Nerul" => [
                "city" => "Mumbai",
                "state" => "Maharashtra",
                "pincode" => "400706"
            ],
            "Kharghar" => [
                "city" => "Mumbai",
                "state" => "Maharashtra",
                "pincode" => "410210"
            ],
            "Faridabad 15" => [
                "city" => "Faridabad",
                "state" => "Haryana",
                "pincode" => "121007"
            ],
            "Faridabad 21D" => [
                "city" => "Faridabad",
                "state" => "Haryana",
                "pincode" => "121012"
            ],
            "Charkhi Dadri" => [
                "city" => "Charkhi Dadri",
                "state" => "Haryana",
                "pincode" => "127306"
            ],
            "Mahavir Marg" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001"
            ],
            "Rama Mandi" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144023"
            ],
            "Tanda Road" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001"
            ],
            "Model Town" => [
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144003"
            ],
            "Greater Noida" => [
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201306"
            ],
            "Greater Kailash" => [
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110048"
            ]
        ]
    ];

    /**
     * Returns Delivery Location
     *
     * @param string $delivery_institution
     * @param string $branch
     * @return array|boolean
     */
    public static function getSchoolLocation($delivery_institution, $branch)
    {
        // Checking for group
        if (array_key_exists($delivery_institution, self::SCHOOL_ADDRESS_MAPPING)) {
            $locations = self::SCHOOL_ADDRESS_MAPPING[$delivery_institution];

            // Checkng for locations
            if (array_key_exists($branch, $locations)) {
                return $locations[$branch];
            }
        }

        return false;
    }

    const ORDERS_LIST = [1339071856674,
        1339072610338,
        1339079196706,
        1339079753762,
        1339088568354,
        1339092434978,
        1339093123106,
        1339095416866,
        1339112456226,
        1339112816674,
        1339113340962,
        1339113504802,
        1339113734178,
        1339114160162,
        1339114782754,
        1339115012130,
        1339115241506,
        1339115470882,
        1339115733026,
        1339115995170,
        1339116159010,
        1339116716066,
        1339116879906,
        1339116912674,
        1339117109282,
        1339117207586,
        1339117404194,
        1339117502498,
        1339117764642,
        1339117961250,
        1339118125090,
        1339118288930,
        1339118485538,
        1339119206434,
        1339119927330,
        1339120025634,
        1339138441250,
        1339138474018,
        1339138637858,
        1351923138594,
        1351924449314,
        1351924842530,
        1351942012962,
        1352070889506,
        1352071479330
    ];
}


