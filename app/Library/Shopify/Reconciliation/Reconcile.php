<?php

namespace App\Library\Shopify\Reconciliation;

use App\Library\Shopify\Reconciliation\File;
use App\Library\Shopify\Reconciliation\Offline;
use App\Library\Shopify\Reconciliation\Online;
use App\Library\Shopify\Reconciliation\Source;

/**
 * Class Reconcile
 */
class Reconcile
{
    // Modes available
    const MODE_SANDBOX = 1;
    const MODE_SETTLE = 2;

    public static function IsValidMode(int $mode) {
        return in_array($mode, [self::MODE_SANDBOX, self::MODE_SETTLE]);
    }

    public static function IsValidSource(int $source) {
        return isset(File::$source[$source]);
    }

    public static function GetSourceClass(int $source) {
        return File::$source[$source];
    }

    /**
     * Helper function to get an instance
     * @param File $File
     * @param int $mode
     * @param int $orgID
     *
     * @return Offline|Online
     *
     * @throws \Exception
     */
    public static function Instance(File $File, int $mode, int $orgID = 0) {
        if (!in_array($mode, [self::MODE_SANDBOX, self::MODE_SETTLE])) {
            throw new \Exception('Invalid mode given');
        }

        $Instance = null;
        if ($File->GetSourceClass() == Source\PayU::class
            || $File->GetSourceClass() == Source\PaytmGateway::class
            || $File->GetSourceClass() == Source\PaytmApp::class
            || $File->GetSourceClass() == Source\Razorpay::class) {
            $Instance = new Online($File, $mode);
        }
        else if($File->GetSourceClass() == Source\Manual::class) {
            $Instance = new Manual($File, $mode);
        }
        else {
	        $Instance = new Offline($File, $mode);
        }

	    return $Instance;
    }
}
