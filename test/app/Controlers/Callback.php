<?php

namespace App\Controlers;

use Chukdo\Http\Controler;
use Chukdo\Http\Response;
use Chukdo\Http\Input;

class Callback extends Controler
{
    public function index( Input $inputs, Response $response ): Response
    {
        return $response->content( 'TEST Controler: ' . (string) $inputs );
    }
}