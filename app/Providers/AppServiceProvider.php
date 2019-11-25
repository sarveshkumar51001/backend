<?php

namespace App\Providers;

use App\Library\Shopify\DataRaw;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Preventing error reporting for deprecated functions
        error_reporting(E_ALL ^ E_DEPRECATED);

        // Creating custom header for excel import for slugged with count
        HeadingRowFormatter::extend('shopify_bulk_upload', function($value) {
            return DataRaw::getHeaderName($value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
