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

    const HAYDEN_REYNOTT = 'H&R';

    const PAGINATE_LIMIT = 100;

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

    const PaymentSettlementStatus = 'settlement_status';
    const PaymentSettlementMode = 'settlement_mode';
    const PaymentReturnedDate = 'return_date';
    const PaymentReturnedBy = 'return_by';
    const PaymentUpdatedAt = 'order_update_at';
    const PaymentLiquidationDate = 'liquidation_date';
    const PaymentSettledDate = 'settled_date';
    const PaymentSettledBy = 'settled_by';
    const PaymentDepositDate = 'deposit_date';
    const PaymentAmount = 'amount';
    const PaymentProcessed = 'processed';


    const PAYMENT_SETTLEMENT_STATUS_RETURNED = 'returned';
    const PAYMENT_SETTLEMENT_STATUS_SETTLED = 'settled';
    const PAYMENT_SETTLEMENT_STATUS_DEFAULT = 'pending';

    const PAYMENT_RECONCILIATION_STATUS = [
        self::PAYMENT_SETTLEMENT_STATUS_DEFAULT,
        self::PAYMENT_SETTLEMENT_STATUS_SETTLED,
        self::PAYMENT_SETTLEMENT_STATUS_RETURNED
    ];

    const PAYMENT_SETTLEMENT_MODE_BANK = 'bank';
    const PAYMENT_SETTLEMENT_MODE_MANUAL = 'manual';

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

    public function getOrder() {
        return $this->shopify_order_name ?? $this->order_id;
    }

    const SCHOOL_ADDRESS_MAPPING = [
        "Apeejay" => [
            "Sheikh Sarai" => [
                "code" => "VALSS",
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017",
                "is_higher_education" => false,
                "address" => 'Apeejay School Sheikh Sarai, Phase 1, New Delhi-110017'
            ],
            "Sheikh Sarai International" => [
                "code" => "VALSSI",
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017",
                "is_higher_education" => false,
                "address" => ''
            ],
            "Pitampura" => [
                "code" => "VALPIT",
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110034",
                "is_higher_education" => false,
                "address" => 'Apeejay School Pitampura, Plot No10, road No:42, Sainik Vihar,Pitampura, Delhi-110034',
            ],
            "Saket" => [
                "code" => "VALSKT",
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110017",
                "is_higher_education" => false,
                "address" => 'Apeejay school Saket, J block  Saket, New Delhi-110017'
            ],
            "Noida" => [
                "code" => "VALNVD",
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201301",
                "is_higher_education" => false,
                "address" => 'Apeejay School Sector 16, A Noida-201301'
            ],
            "Nerul" => [
                "code" => "VALNRL",
                "city" => "Mumbai",
                "state" => "Maharashtra",
                "pincode" => "400706",
                "is_higher_education" => false,
                "address" => 'Apeejay School Sector XV, Nerul, Navi Mumbai-400706'
            ],
            "Kharghar" => [
                "code" => "VALKHG",
                "city" => "Mumbai",
                "state" => "Maharashtra",
                "pincode" => "410210",
                "is_higher_education" => false,
                "address" => 'Apeejay School, Sector 21, Kharghar, Navi Mumbai-410210'
            ],
            "Faridabad 15" => [
                "code" => "VALFBD",
                "city" => "Faridabad",
                "state" => "Haryana",
                "pincode" => "121007",
                "is_higher_education" => false,
                "address" => 'Apeejay School Sector 15 Faridabad. NCR,121007.'
            ],
            "Faridabad 21D" => [
                "code" => "VALSVG",
                "city" => "Faridabad",
                "state" => "Haryana",
                "pincode" => "121012",
                "is_higher_education" => false,
                "address" => 'Apeejay School , Sector 21 D, Faridabad, NCR 121007'
            ],
            "Charkhi Dadri" => [
                "code" => "VALCKD",
                "city" => "Charkhi Dadri",
                "state" => "Haryana",
                "pincode" => "127306",
                "is_higher_education" => false,
                "address" => ''
            ],
            "Mahavir Marg" => [
                "code" => "VALMM",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001",
                "is_higher_education" => false,
                "address" => 'Apeejay School Bhagwan Mahavir Marg Mahavir Margr, Jalandhar-144001'
            ],
            "Rama Mandi" => [
                "code" => "VALRM",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144023",
                "is_higher_education" => false,
                "address" => ''
            ],
            "Tanda Road" => [
                "code" => "VALTR",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001",
                "is_higher_education" => false,
                "address" => ''
            ],
            "Model Town" => [
                "code" => "VALMT",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144003",
                "is_higher_education" => false,
                "address" => ''
            ],
            "Greater Noida" => [
                "code" => "VALAIS",
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201306",
                "is_higher_education" => false,
                "address" => 'Apeejay International School 1, Institutional Area, Gamma Sector,Surajpur Kansa Road, PO Tughalpur,Greater Noida-201308'
            ],
            "Greater Kailash" => [
                "code" => "VALGK",
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110048",
                "is_higher_education" => false,
                "address" => ''
            ],
            "ACFA Mahavir Marg" => [
                "code" => "",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144001",
                "is_higher_education" => true,
                "address" => ''
            ],
            "AIMTC Rama Mandi" => [
                "code" => "",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144023",
                "is_higher_education" => true,
                "address" => ''
            ],
            "AID New Delhi" => [
                "code" => "",
                "city" => "New Delhi",
                "state" => "New Delhi",
                "pincode" => "110062",
                "is_higher_education" => true,
                "address" => ''
            ],
            "AIMC Dwarka" => [
                "code" => "",
                "city" => "New Delhi",
                "state" => "New Delhi",
                "pincode" => "110077",
                "is_higher_education" => true,
                "address" => ''
            ],
            "ASM Dwarka" => [
                "code" => "",
                "city" => "New Delhi",
                "state" => "New Delhi",
                "pincode" => "110077",
                "is_higher_education" => true,
                "address" => ''
            ],
            "AITCS Greater Noida" => [
                "code" => "",
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201308",
                "is_higher_education" => true,
                "address" => ''
            ],
            "AITSM Greater Noida" => [
                "code" => "",
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201308",
                "is_higher_education" => true,
                "address" => ''
            ],
            "AITSAP Greater Noida" => [
                "code" => "",
                "city" => "Noida",
                "state" => "UP",
                "pincode" => "201308",
                "is_higher_education" => true,
                "address" => ''
            ],
            "SPGC Charkhi Dadri" => [
                "code" => "",
                "city" => "Charkhi Dadri",
                "state" => "Haryana",
                "pincode" => "127306",
                "is_higher_education" => true,
                "address" => ''
            ]
        ],
        "Reynott" => [
            "Reynott Academy Jalandhar" => [
                "code" => "",
                "city" => "Jalandhar",
                "state" => "Punjab",
                "pincode" => "144003",
                "is_higher_education" => false,
                "address" => ''
            ]
        ],
        "H&R" => [
            "Plot 23 Gurgaon" => [
                "code" => "",
                "city" => "Gurugram",
                "state" => "Haryana",
                "pincode" => "122003",
                "is_higher_education" => false,
                "address" => ''
            ],
            "Dwarka" => [
                "code" => "",
                "city" => "Delhi",
                "state" => "Delhi",
                "pincode" => "110037",
                "is_higher_education" => false,
                "address" => ''
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
        return array_merge(array_keys(self::SCHOOL_ADDRESS_MAPPING["Apeejay"]),array_keys(self::SCHOOL_ADDRESS_MAPPING['Reynott']),array_keys(self::SCHOOL_ADDRESS_MAPPING["H&R"]));
    }

}


