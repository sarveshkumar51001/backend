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
        'upload_date'
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

    const school_address_mapping = [

        "Apeejay Sheikh Sarai" => [
            "city" => "Delhi",
            "state" => "Delhi",
            "pincode" => "110017"
        ],
        "Apeejay Pitampura" => [
            "city" => "Delhi",
            "state" => "Delhi",
            "pincode" => "110034"
        ],
        "Apeejay Saket" => [
            "city" => "Delhi",
            "state" => "Delhi",
            "pincode" => "110017"
        ],
        "Apeejay Noida" => [
            "city" => "Noida",
            "state" => "UP",
            "pincode" => "201301"
        ],
        "Apeejay Nerul" => [
            "city" => "Mumbai",
            "state" => "Maharashtra",
            "pincode" => "400706"
        ],
        "Apeejay Kharghar" => [
            "city" => "Mumbai",
            "state" => "Maharashtra",
            "pincode" => "410210"
        ],
        "Apeejay Faridabad 15" => [
            "city" => "Faridabad",
            "state" => "Haryana",
            "pincode" => "121007"
        ],
        "Apeejay Faridabad 21 D" => [
            "city" => "Faridabad",
            "state" => "Haryana",
            "pincode" => "121012"
        ],
        "Apeejay Charkhi Dadri" => [
            "city" => "Charkhi Dadri",
            "state" => "Haryana",
            "pincode" => "127306"
        ],
        "Apeejay Mahavir Marg" => [
            "city" => "Jalandhar",
            "state" => "Punjab",
            "pincode" => "144001"
        ],
        "Apeejay Rama Mandi" => [
            "city" => "Jalandhar",
            "state" => "Punjab",
            "pincode" => "144023"
        ],
        "Apeejay Tanda Road" => [
            "city" =>"Jalandhar",
            "state" => "Punjab",
            "pincode" => "144001"
        ],
        "Apeejay Greater Noida" => [
            "city" => "Noida",
            "state" => "UP",
            "pincode" => "201306"
        ],
        "Apeejay Greater Kailash" => [
            "city" => "Delhi",
            "state" => "Delhi",
            "pincode" => "110048"
        ],
        "Apeejay Sheikh Sarai International" => [
            "city" => "Delhi",
            "state" => "Delhi",
            "pincode" => "110017"
        ]];
}