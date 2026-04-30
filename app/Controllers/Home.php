<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home');
    }

    public function test(int $id, int $id2): string
    {
        return view('test',["id1"=>$id,"id2"=>$id2]);
    }
}
