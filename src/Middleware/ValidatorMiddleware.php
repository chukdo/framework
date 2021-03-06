<?php

namespace Chukdo\Middleware;

use Chukdo\Bootstrap\ServiceException;
use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Response;
use ReflectionException;

class ValidatorMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected array $validators = [];

    /**
     * @var ErrorMiddlewareInterface
     */
    protected ?ErrorMiddlewareInterface $errorMiddleware;

    /**
     * ValidatorMiddleware constructor.
     *
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     */
    public function __construct( array $validators, ErrorMiddlewareInterface $errorMiddleware = null )
    {
        $this->validators      = $validators;
        $this->errorMiddleware = $errorMiddleware;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return Response
     * @throws ServiceException
     * @throws ReflectionException
     */
    public function process( Dispatcher $dispatcher ): Response
    {
        $validate = $dispatcher->request()
                               ->validate( $this->validators );
        if ( $validate->fails() ) {
            return ( $this->errorMiddleware ?? new ErrorMiddleware() )->errorMessage( $validate->errors() )
                                                                      ->process( $dispatcher );
        }

        $dispatcher->attribute( 'inputs', $validate->validated() );

        return $dispatcher->handle();
    }
}