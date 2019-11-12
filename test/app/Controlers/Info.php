<?php

namespace App\Controlers;

use Chukdo\Http\Controler;
use Chukdo\Http\Response;
use Chukdo\Http\Input;

class Info extends Controler
{
    public function index( Input $inputs, Response $response ): Response
    {
        return $response->header( 'X-Info', 'ok' )
                        ->content( 'Info Controler: ' . (string)$inputs );
    }
}