<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
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
     * @param Dispatcher $delegate
     * @return Response
     */
    public function process( Dispatcher $delegate ): Response
    {
        $response = $delegate->response();

        switch( $this->errors->render() ) {
            case 'cli' :
                $response->content($this->errors->toConsole());
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