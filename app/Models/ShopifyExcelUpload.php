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

	public static $modesTitle = [
		self::MODE_CASH => 'Cash',
		self::MODE_CHEQUE => 'Cheque',
		self::MODE_DD => 'DD',
		self::MODE_PDC => 'PDC Cheque',
		self::MODE_ONLINE => 'Online',
		self::MODE_PAYTM => 'Paytm QR Code',
		self::MODE_NEFT => 'NEFT'
	];
}