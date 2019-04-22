<?php

namespace Chukdo\Middleware;

use Chukdo\Http\Request;
use Chukdo\Http\Response;

class Dispatcher
{
    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @param callable $middleware
     * @return Dispatcher
     */
    public function pipe(callable $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function handle(Request $request, Response $response): Response
    {
        $middleware = $this->getMiddleware();
        $this->index++;

        if (is_callable($middleware)) {
            return $middleware($request, $response, [$this, 'handle']);
        }

        return $response;
    }

    /**
     * @return callable|null
     */
    private function getMiddleware()
    {
        if (isset($this->middlewares[$this->index])) {
            return $this->middlewares[$this->index];
        }

        return null;
    }
}