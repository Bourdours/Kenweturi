<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    public function register()
    {
        return view('Auth/register');
    }

    public function login()
    {
        return view('Auth/login');
    }
}