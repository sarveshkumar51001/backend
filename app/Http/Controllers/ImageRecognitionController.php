<?php

namespace App\Http\Controllers;

use Aws\S3;

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

        return view('image-recognition.search-by-image', ['url_to_file' => $url]);
    }

    private function UploadToS3() {
		$S3 = S3\S3Client::factory([
			'credentials' => [
				'key' => env('S3_IMAGE_REKO_KEY'),
				'secret' => env('S3_IMAGE_REKO_SECRET')
			],
			'version' => 'latest',
			'region'  => env('S3_IMAGE_REKO_REGION')
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

    private function GetSignedURL($S3, $key) {
	    $cmd = $S3->getCommand('GetObject', [
				'Bucket' => env('S3_IMAGE_REKO_BUCKET'),
				'Key'    => $key,
	    ]);

	    $request = $S3->createPresignedRequest($cmd, '+5 minutes');

	    return (string) $request->getUri();
    }
}
