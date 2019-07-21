<?php

/*
 * |--------------------------------------------------------------------------
 * | Webhook Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register webhook routes for your application. These
 * | routes are loaded by the RouteServiceProvider within a group which
 * | contains the "webhook" middleware group. Now create something great!
 * |
 */

Route::post("{path}", function () {
    return response()->json([
        'webhook_id' => request()->webhook_id
    ], 200);
})->where('path', '.*');

Route::fallback(function () {
    return response()->json([
        'error' => 'Not Found'
    ], 404);
});