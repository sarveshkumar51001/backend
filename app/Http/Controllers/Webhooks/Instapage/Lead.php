<?php
namespace App\Http\Controllers\Webhooks\Instapage;

use Illuminate\Http\Request;
use App\Library\Shopify\API;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\crud\ZohoOAuth;
// use zcrmsdk\crm\crud\ZCRMRestClient;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
// require 'vendor/autoload.php';

$configuration = [
	'client_id' => env('ZOHO_CLIENT_ID'),
	'client_secret' => env('ZOHO_SECRET'),
	'redirect_uri' => env('ZOHO_REDIRECT'),
	'currentUserEmail'=> env('ZOHO_USER')
];

class Lead
{
	public function __construct()
    {
        $configuration = array(
			'client_id' => env('ZOHO_CLIENT_ID'),
			'client_secret' => env('ZOHO_SECRET'),
			'redirect_uri' => env('ZOHO_REDIRECT'),
			'currentUserEmail'=> env('ZOHO_USER'),
			'token_persistence_path'=> 'C:\xampp\htdocs\backend\storage\zcrm'   	
		);

        ZCRMRestClient::initialize($configuration);
    }

	// public function setclient()
	// {
	//     ZCRMRestClient::initialize($configuration);
	    // $oAuthClient = ZohoOAuth::getClientInstance();
	    // $grantToken = 'xxxx';
	    // $oAuthTokens = $oAuthClient->generateAccessToken($grantToken);
	// }

	public function create(Request $request)
	{
		// $client_response = $this->setclient();

		// $configuration = array(
		// 	'Client ID' => env('ZOHO_CLIENT_ID'),
		// 	'Client Secret' => env('ZOHO_SECRET'),
		// 	'Client Name' => env('ZOHO_CLIENT'),
		// 	'Client Domain' => env('ZOHO_CLIENT_DOMAIN'),
		// 	'currentUserEmail'=>'pankaj@valedra.com')
		// );

		// ZCRMRestClient::initialize($configuration);

		$module = ZCRMModule::getInstance('Leads');
		$record = $this->create_lead_array($request);

		$response = $module->createRecords([$record]);
		$entityResponses = $response->getEntityResponses();

		return response(200, json_encode($entityResponses));
	}

	public function create_lead_array($data)
	{	
		$records=array();
		$record = ZCRMRecord::getInstance('Leads', null);

		$record->setFieldValue('First_Name', explode(' ', $data['Full Name'])[0]);
		// if ($data['Full Name'])[1] != null)
		// { $record->setFieldValue('Last_Name', explode(' ', $data['Full Name'])[1]);	}

		$record->setFieldValue('Email', $data['Email']);
		$record->setFieldValue('Phone', $data['Phone Number']);
		$record->setFieldValue('Country', $data['Country']);
		$record->setFieldValue('Best Time to Call', $data['Best Time to Call']);
		$record->setFieldValue('Count of Minor Travellers', $data['Number of People Travelling']);
		$record->setFieldValue('Description', $data['Your Message']);

		return $record;
	}	
}