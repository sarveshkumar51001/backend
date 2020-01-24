<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/redirect', 'SocialAuthGoogleController@redirect');
Route::get('/callback', 'SocialAuthGoogleController@callback');

Route::prefix('shopify')->group(function() {
    Route::get('/orders', 'OrderController@index');
    Route::get('/orders/{id}', 'OrderController@view');
    Route::get('/orders/create', 'OrderController@create');
    Route::post('/orders/create', 'OrderController@create');
    Route::get('/orders/update', 'OrderController@update');
    Route::get('/customers', 'CustomerController@index');
    Route::get('/customers/{id}/regenerate_rec/{customer_id}', 'CustomerController@regenerate_rec');
    Route::get('/customers/profiler', 'CustomerController@profiler');
    Route::get('/customers/profiler/{id}', 'CustomerController@profiler_response');
    Route::get('/customers/{id}', 'CustomerController@view');
    Route::get('/products', 'ProductController@index');
    Route::get('/products/{id}', 'ProductController@view');
    Route::get('/search', 'SearchController@index');
    Route::prefix('bulkupload')->group(function () {
        Route::get('/', 'ShopifyController@upload')->name('bulkupload.upload');
        Route::post('/preview', 'ShopifyController@upload_preview')->name('bulkupload.upload_preview');
        Route::get('/preview', function () {
            return redirect()->route('bulkupload.upload');
        });
        Route::get('/previous/uploads', 'ShopifyController@previous_uploads')->name('bulkupload.previous_uploads');
        Route::get('/previous/orders', 'ShopifyController@previous_orders')->name('bulkupload.previous_orders');
        Route::get('/previous/file_download/{id}', 'ShopifyController@download_previous')->name('bulkupload.download_previous');
        Route::get('/search', 'App\Http\Controllers\BulkUpload\SearchController@search')->name('bulkupload.search');

    });
});

    Route::group(['prefix' => 'students'], function() {
        Route::get('/search', 'StudentController@index')->name('search.students');
        Route::post('/search-by-details', 'StudentController@search_by_student_details')->name('search.student-details');
        Route::post('/search-by-enrollment-no', 'StudentController@search_by_student_enrollment_no')->name('search.student-enrollment-no');
        Route::fallback(function () {
            return redirect()->route('search.students');
        });

    });
    Route::get('/transactions','TransactionController@index')->name('orders.transactions');
    Route::post('/get/transactions', 'TransactionController@search_transactions_by_location')->name('get.transactions');
    Route::get('/get/transactions','TransactionController@search_transactions_by_location')->name('self.transactions');

Route::prefix('imagereco')->group(function() {
        Route::get('/', 'ImageRecognitionController@listAllPeople')->name('imagereco.list-all-people');
        Route::post('/', 'ImageRecognitionController@listAllPeople_result')->name('imagereco.list-all-people-result');
        Route::get('/search/name', 'ImageRecognitionController@searchByName')->name('imagereco.search-by-name');
        Route::post('/search/name', 'ImageRecognitionController@searchByName_result')->name('imagereco.search-by-name-result');
        Route::get('/search/image', 'ImageRecognitionController@searchByImage')->name('imagereco.search-by-image');
        Route::post('/search/image', 'ImageRecognitionController@searchByImage_result')->name('imagereco.search-by-image-result');
    });

Route::group(['prefix' => 'api/v1/', /*'middleware' => ['auth']*/], function() {
	Route::get('upload/{id}', function ($id) {
		return (new \App\Http\Controllers\Api\OrderController())->get_upload_details($id);
	});
});

        Auth::routes();

        use Oseintow\Shopify\Facades\Shopify;


// @todo remove me when go live

/**
 * Get the access token request
 */
Route::get("install_shop",function()
{
	$shopUrl = "valedra.myshopify.com";
	$scope = ["read_products","write_products","read_orders", "write_orders", "read_customers", "write_customers"];
	$redirectUrl = "http://localhost:8000/process_oauth_result";

	$shopify = Shopify::setShopUrl($shopUrl);
	return redirect()->to($shopify->getAuthorizeUrl($scope,$redirectUrl));
});

Route::get("process_oauth_result",function(\Illuminate\Http\Request $request)
{
	$shopUrl = "valedra.myshopify.com";
	$accessToken = Shopify::setShopUrl($shopUrl)->getAccessToken($request->code);

	dd("Access token is " . $accessToken);
});

Route::get("/qwilr/quote_status", function () {
	$json = '{
  "total": 490,
  "quote_id": "12345",
  "opportunity_id": "10002203033"
  "checkout_url": "https://backend-valedra.myshopify.com/4333928494/invoices/537541f81b14d477bca5a55823e1a35d",
  "qoute_pdf_url": "https://vault.qwilr.com/G3kMoHnoGoctWc4-y1uO4-OSE4yY4w-project.pdf",
  "line_items": [
    {
      "code": "BD-TF",
      "desc": "Tournament Fee",
      "qty": 1,
      "unit_price": 180,
      "total_price": 180
    },
    {
      "code": "BD-TF-TA",
      "desc": "Addon: Accommodation (Twin-Sharing)",
      "qty": 1,
      "unit_price": 310,
      "total_price": 310
    }
  ]
}';
	return response()->json(json_decode($json));
})->middleware('cors');
