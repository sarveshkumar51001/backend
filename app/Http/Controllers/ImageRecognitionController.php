<?php
namespace App\Http\Controllers;

use Aws\S3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use function GuzzleHttp\json_encode;

class ImageRecognitionController extends BaseController
{

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$url = '';
		if (request()->isMethod('post')) {
			$url = $this->UploadToS3();
		}

		return view('image-recognition.search-by-image', [
			'url_to_file' => $url
		]);
	}

	public function searchByName()
	{
		return view('image-recognition.search-by-name');
	}

	public function searchByName_result(Request $request)
	{
		try {

			$post_request = array(
				"request_type" => "search",
				"request_parameters" => array(
					"search_type" => "text",
					"text_type" => "$request->type",
					"search_text" => "$request->name",
					"search_tag" => ""
				)
			);

			$url = env('IMAGE_REKO_LAMBDA_API') . '/image_lambda/search-api';
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode($post_request),
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json"
				)
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				Log::error($err);
				abort(500);
			}
			$response_arr = json_decode($response);
			$results = array();

			foreach ($response_arr as $response) {
				$result = ($this->GetSignedURL(env('IMAGE_REKO_S3_BUCKET'), $response));
				array_push($results, $result);
			}

			return view('image-recognition.search-by-name')->with('results', $results);
		} catch (\Exception $e) {
			Log::error($e);
			abort(500);
		}

		return view('image-recognition.search-by-name');
	}

	public function listAllPeople()
	{
		return view('image-recognition.list-all-people');
	}

	public function listAllPeople_result(Request $request)
	{
		try {

			$post_request = array(
				"request_type" => "list_people",
				"request_parameters" => array(
					"list_type" => "$request->tag",
					"list_organization" => "$request->organization"
				)
			);


			$url = env('IMAGE_REKO_LAMBDA_API') . '/image_lambda/search-api';
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode($post_request),
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json"
				)
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);
			if ($err) {
				Log::error($err);
				abort(500);
			}
			$response_arr = json_decode($response);
			if(array_key_exists('Message', $response_arr))
				return view('image-recognition.list-all-people')->with('error', $response_arr->Message);

			$peoples = array();

			foreach ($response_arr as $response) {
				$people = ($this->GetSignedURL(env('IMAGE_REKO_S3_BUCKET'), $response));
				array_push($peoples, $people);
			}

			return view('image-recognition.list-all-people')->with('peoples', $peoples);
		} catch (\Exception $e) {
			Log::error($e);
			abort(500);
		}
	}

	private function UploadToS3()
	{
		$S3 = S3\S3Client::factory([
			'credentials' => [
				'key' => env('S3_IMAGE_REKO_KEY'),
				'secret' => env('S3_IMAGE_REKO_SECRET')
			],
			'version' => 'latest',
			'region' => env('S3_IMAGE_REKO_REGION')
		]);

		$key = 'primary_collection/' . request()->file->getClientOriginalName();

		try {
			$S3->putObject([
				'Bucket' => env('S3_IMAGE_REKO_BUCKET'),
				'Key' => $key,
				'SourceFile' => request()->file->getPathName(),
				'StorageClass' => 'REDUCED_REDUNDANCY'
			]);

			$url = $this->GetSignedURL($S3, $key);
		} catch (S3\Exception\S3Exception $e) {
			$url = $e->getMessage();
		} catch (\Exception $e) {
			$url = $e->getMessage();
		}

		return $url;
	}

	private function GetSignedURL($S3, $key)
	{
		$S3 = S3\S3Client::factory([
			'credentials' => [
				'key' => env('IMAGE_REKO_S3_KEY'),
				'secret' => env('IMAGE_REKO_S3_SECRET')
			],
			'version' => 'latest',
			'region' => env('IMAGE_REKO_S3_REGION')
		]);

		$cmd = $S3->getCommand('GetObject', [
			'Bucket' => env('IMAGE_REKO_S3_BUCKET'),
			'Key' => $key
		]);

		$request = $S3->createPresignedRequest($cmd, '+5 minutes');

		return (string) $request->getUri();
	}
}
