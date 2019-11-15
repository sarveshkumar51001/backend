<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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

        Validator::extend('not_exponential', function($attribute, $value, $parameters) {
            if(preg_match('/^\d+$/',$value)){
                return true;
            } else{
                return false;
            }
        });
        Validator::extend('amount', function($attribute, $value, $parameters) {
            if(preg_match('/^\d+(.\d{1,2})?$/',$value)){
                return true;
            } else{
                return false;
            }
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
