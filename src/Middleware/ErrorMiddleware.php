<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Http;
use Chukdo\Helper\Str;
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

        if ( Cli::runningInConsole() ) {
            $response->content($this->errors->toConsole());
        }
        else {
            switch ( Str::extension(Http::server('SCRIPT_URI')) ) {
                case 'json' :
                    $response->json($this->errors);
                    break;
                case 'xml' :
                    $response->xml($this->errors);
                    break;
                default :
                    $response->html($this->errors->toHtml(null, '#dd0000'));
            }
        }


        return $response->status(412);
    }
}