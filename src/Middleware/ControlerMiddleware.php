<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Response;

class ControlerMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    protected $controler = '';

    /**
     * @var string
     */
    protected $action = '';

    /**
     * ControlerMiddleware constructor.
     *
     * @param String $uri
     */
    public function __construct( String $uri )
    {
        list( $this->controler, $this->action ) = explode( '@', $uri );
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return Response
     */
    public function process( Dispatcher $dispatcher ): Response
    {
        $inputs = $dispatcher->attribute( 'inputs' )
            ?: $dispatcher->request()
                ->inputs();

        $controler = $this->controler;
        $action    = $this->action;

        return ( new $controler() )->$action( $inputs, $dispatcher->response() );
    }
}