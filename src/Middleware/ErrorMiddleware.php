<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Helper\HttpRequest;
use Chukdo\Http\Response;
use Chukdo\Json\Message;

class ErrorMiddleware implements ErrorMiddlewareInterface
{
    /**
     * @var ErrorMiddlewareInterface
     */
    protected $errors = null;

    /**
     * @param Message $errors
     * @return ErrorMiddlewareInterface
     */
    public function errorMessage( Message $errors ): ErrorMiddlewareInterface
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param Dispatcher $dispatcher
     * @return Response
     */
    public function process( Dispatcher $dispatcher ): Response
    {
        $response = $dispatcher->response();

        switch ( HttpRequest::render() ) {
            case 'cli' :
                $response->content($this->errors->toConsole(null, 'red'));
                break;
            case 'json' :
                $response->json($this->errors);
                break;
            case 'xml' :
                $response->xml($this->errors);
                break;
            default :
                $response->html($this->errors->toHtml(null, '#dd0000'));
        }

        return $response->status(412);
    }
}