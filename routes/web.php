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
Route::prefix('imagereco')->group(function() {
    Route::get('/', 'ImageRecognitionController@listAllPeople')->name('imagereco.list-all-people');
    Route::post('/', 'ImageRecognitionController@listAllPeople_result')->name('imagereco.list-all-people-result');
    Route::get('/search/name', 'ImageRecognitionController@searchByName')->name('imagereco.search-by-name');
    Route::post('/search/name', 'ImageRecognitionController@searchByName_result')->name('imagereco.search-by-name-result');
    Route::get('/search/image', 'ImageRecognitionController@searchByImage')->name('imagereco.search-by-image');
    Route::post('/search/image', 'ImageRecognitionController@searchByImage_result')->name('imagereco.search-by-image-result');
});
Route::prefix('bulkupload')->group(function() {
	Route::get('/', 'ShopifyController@ShopifyBulkUpload')->name('bulkupload.ShopifyBulkUpload');
	Route::post('/preview', 'ShopifyController@ShopifyBulkUpload_result')->name('bulkupload.ShopifyBulkUpload-result');
	Route::get('/previous/uploads','ShopifyController@List_All_Files')->name('bulkupload.List_All_Files');
	Route::get('/previous/orders','ShopifyController@List_All_Orders')->name('bulkupload.List_All_Orders');
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
