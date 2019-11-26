<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
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

        Validator::extend('not_exponential', function ($attribute, $value, $parameters) {
            if (preg_match('/^\d+$/', $value)) {
                return true;
            } else {
                return false;
            }
        });
        Validator::extend('amount', function ($attribute, $value, $parameters) {
            if (preg_match('/^\d+(.\d{1,2})?$/', $value)) {
                return true;
            } else {
                return false;
            }
        });

        // Creating custom header for excel import for slugged with count
        HeadingRowFormatter::extend('shopify_bulk_upload', function ($value) {
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
