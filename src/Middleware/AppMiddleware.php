<?php

namespace Chukdo\Middleware;

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
     * @var Closure
     */
    protected $error;

    /**
     * AppMiddleware constructor.
     * @param Closure $closure
     */
    public function __construct( \Closure $closure )
    {
        $this->closure = $closure;
    }

    /**
     * @param array $validators
     * @return AppMiddleware
     */
    public function validator( array $validators ): self
    {
        $this->validators = $validators;

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

        if( $validate->fails() ) {
            return $validate->errors()
                ->response($delegate->response())
                ->send();
        }

        return ($this->closure)($validate->validated(), $delegate->response());
    }
}