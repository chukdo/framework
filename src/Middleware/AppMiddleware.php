<?php

namespace Chukdo\Middleware;

use Chukdo\Http\Request;
use Chukdo\Http\Response;
use Chukdo\Json\Message;
use Chukdo\Routing\Route;
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
     * @param array        $validators
     * @param Closure|null $error
     * @return Route
     */
    public function validator( array $validators, Closure $error = null ): self
    {
        $this->validators = $validators;
        $this->error      = $error;

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
            return $this->error($validate->errors(), $delegate->request(), $delegate->response());
        }

        return ($this->closure)($validate->validated(), $delegate->response());
    }

    /**
     * @param Message  $errors
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    protected function error( Message $errors, Request $request, Response $response ): Response
    {
        if( $this->error ) {
            return ($this->error)($errors, $response);
        }

        switch( $request->render() ) {
            case 'json' :
                $response->json($errors);
                break;
            case 'xml' :
                $response->xml($errors);
                break;
            default :
                $response->html($errors->toHtml('Input Errors', '#dd0000'));
        }

        $response->send();

        return $response;
    }
}