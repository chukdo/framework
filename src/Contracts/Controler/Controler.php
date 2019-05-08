<?php

namespace Chukdo\Contracts\Controler;

use Chukdo\Http\Request;

/**
 * Interface des controlers
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Controler
{
    /**
     * Controler constructor.
     * @param Request $request
     */
    public function __construct(Request $request);

    /**
     * @return Request
     */
    public function request(): Request;
}
