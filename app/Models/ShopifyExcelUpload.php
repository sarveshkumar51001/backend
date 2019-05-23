<?php

namespace App\Models;

class ShopifyExcelUpload extends Base
{
	protected $connection = 'mongodb';
	protected $collection = 'shopify_excel_uploads';
	protected $guarded = [];

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

	public static $modesTitle = [
		self::MODE_CASH => 'Cash',
		self::MODE_CHEQUE => 'Cheque',
		self::MODE_DD => 'DD',
		self::MODE_PDC => 'PDC Cheque',
		self::MODE_ONLINE => 'Online'
	];
}