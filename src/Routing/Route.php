<?php

namespace Chukdo\Routing;

use Closure;

/**
 * Gestion d'une Route.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Route
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Closure
     */
    protected $closure;

    /**
     * Route constructor.
     *
     * @param Router $router
     * @param Closure $closure
     */
    public function __construct( Router $router, Closure $closure )
    {
        $this->router  = $router;
        $this->closure = $closure;
    }
}
