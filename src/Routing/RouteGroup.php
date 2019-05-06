<?php

namespace Chukdo\Routing;

use Closure;

/**
 * Gestion des Routes.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RouteGroup extends RouteAttribute
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
     * RouteGroup constructor.
     * @param Router  $router
     * @param Closure $closure
     */
    public function __construct( Router $router, Closure $closure )
    {
        $this->router  = $router;
        $this->closure = $closure;
    }

    /**
     * @return RouteGroup
     */
    public function route(): self
    {
        $attributes = $this->router->getAttributes();
        $this->router->setAttributes([
            'middleware'      => $this->middlewares,
            'validator'       => $this->validators,
            'errorMiddleware' => $this->errorMiddleware,
            'prefix'          => $this->prefix,
            'namespace'       => $this->namespace,
        ]);

        ($this->closure)();

        $this->router->resetAttributes()
            ->setAttributes($attributes);

        return $this;
    }
}
