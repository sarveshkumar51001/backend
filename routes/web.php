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
Route::get('/orders/create', 'OrderController@create');
Route::post('/orders/create', 'OrderController@create');
Route::get('/orders/update', 'OrderController@update');
Route::get('/customers', 'CustomerController@index');
Route::get('/customers/profiler', 'CustomerController@profiler');
Route::get('/products', 'ProductController@index');



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
