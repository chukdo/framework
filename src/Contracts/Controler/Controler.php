<?php

namespace Chukdo\Contracts\Controler;

use Chukdo\Http\Request;
use Chukdo\Http\Response;
use Chukdo\View\View;

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
     * @param Request  $request
     * @param Response $response
     * @param View     $view
     */
    public function __construct(Request $request, Response $response, View $view);

    /**
     * @return Request
     */
    public function request(): Request;

    /**
     * @return Response
     */
    public function response(): Response;

    /**
     * @return View
     */
    public function view(): View;
}
