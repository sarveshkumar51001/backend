<?php
namespace App\Http\Controllers;

use Aws\S3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use function GuzzleHttp\json_encode;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

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
                    "search_tag" => "$request->tag"
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

    public function searchByImage()
    {
        return view('image-recognition.search-by-image');
    }

    public function searchByImage_result(Request $request)
    {
        $label = join('_', explode(' ', $request->name));
        $organization = join('_', explode(' ', $request->organization));
        $ext = explode('.', $request->file->getClientOriginalName())[1];

        $image_path = "primary_collection/"."$request->tag/"."$organization/"."$label".".$ext"; 
        $file = request()->file->getPathName();

        $response = $this->UploadToS3($image_path, $file);
        $results = array($response);

        try {

            $post_request = array(
                "request_type" => "search",
                "request_parameters" => array(
                    "search_type" => "image",
                    "image_path" => array($image_path),
                    "label" => array($label)
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

            return view('image-recognition.search-by-image')->with('results', $results);
            } catch (\Exception $e) {
                Log::error($e);
                abort(500);
            }

        return view('image-recognition.search-by-image');
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

    private function UploadToS3($key, $file)
    {   
        $S3 = S3\S3Client::factory([
            'credentials' => [
                'key' => env('IMAGE_REKO_S3_KEY'),
                'secret' => env('IMAGE_REKO_S3_SECRET')
            ],
            'version' => 'latest',
            'region' => env('IMAGE_REKO_S3_REGION')
        ]);

        try {

            $S3->putObject([
                'Bucket' => env('IMAGE_REKO_S3_BUCKET'),
                'Key' => $key,
                'SourceFile' => $file,
                'StorageClass' => 'REDUCED_REDUNDANCY'
            ]);

            $url = $this->GetSignedURL($S3, $key);

        } catch (S3\Exception\S3Exception $e) {
            $url = $e->getMessage();
            echo "S3 Exception";
            dd($url);
        } catch (\Exception $e) {
            echo "Exception";
            $url = $e->getMessage();
            dd($url);
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
