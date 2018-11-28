<?php

namespace App\Http\Controllers;

/**
 * Class BaseController
 */
class BaseController extends Controller
{
    /**
     * Just in case of authentication
     *
     * BaseController constructor
     */
    public function __construct() {
        $this->middleware('auth');
     }
}