<?php

namespace Chukdo\Http;

/**
 * Controler
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Controler implements \Chukdo\Contracts\Controler\Controler
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Controler constructor.
     * @param Request $request
     */
    public function __construct( Request $request )
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function request(): Request
    {
        return $this->request;
    }
}