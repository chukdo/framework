<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Http\Response;
use Closure;

class AppMiddleware extends ClosureMiddleware
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @var ErrorMiddlewareInterface
     */
    protected $errorMiddleware = null;

    /**
     * AppMiddleware constructor.
     * @param Closure $closure
     */
    public function __construct( \Closure $closure )
    {
        $this->closure = $closure;
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return AppMiddleware
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->validators      = $validators;
        $this->errorMiddleware = $errorMiddleware;

        return $this;
    }

    /**
     * @param Dispatcher $delegate
     * @return Response
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function process( Dispatcher $delegate ): Response
    {
        $validate = $delegate->request()
            ->validate($this->validators);

        if ( $validate->fails() ) {
            return ( $this->errorMiddleware
                ?: new ErrorMiddleware() )->errorMessage($validate->errors())
                ->process($delegate);
        }

        return ( $this->closure )($validate->validated(), $delegate->response());
    }
}