<?php

namespace App\Controlers;

use Chukdo\Http\Controler;
use Chukdo\Http\Response;

class test extends Controler
{
    public function index($inputs, $response): Response
    {
        return $response->content('TEST Controler: ' . (string) $inputs);
    }
}