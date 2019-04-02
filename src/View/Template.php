<?php

namespace Chukdo\View;

use Chukdo\Helper\Str;
use Chukdo\Json\Json;

/**
 * Moteur de template.
 *
 * @version      1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Template
{
    /**
     * @var string
     */
    protected $file = '';

    /**
     * @var Json
     */
    protected $data = null;

    /**
     * @var View
     */
    protected $view;

    /**
     * Template constructor.
     *
     * @param string $template
     * @param Json   $data
     * @param View   $view
     */
    public function __construct( string $template, Json $data, View $view ) {
        $path = $view->path($template);

        if( !$path[ 'exists' ] ) {
            throw new ViewException(sprintf('Template file [%s] does not exist',
                    $template));
        }

        $this->data($view->getData())
            ->data($view->getData($template))
            ->data($data);

        $this->file = $path[ 'file' ];
        $this->view = $view;
    }

    /**
     * @param iterable|null $data
     *
     * @return Template
     */
    public function data( Iterable $data = null ): self {
        if( !$this->data ) {
            $this->data = new Json();
        }

        $this->data->mergeRecursive($data,
            true);

        return $this;
    }

    /**
     * @param             $data
     * @param string|null $functions
     *
     * @return mixed
     */
    public function v( $data, string $functions = null ) {
        if( $functions ) {
            foreach( Str::split($functions,
                '|') as $function ) {
                $data = $this->$function($data);
            }
        }

        return $data;
    }

    /**
     * @param string      $key
     * @param string|null $functions
     *
     * @return Json|mixed|null
     */
    public function j( string $key, string $functions = null ) {
        return $this->v($this->data->get($key),
            $functions);
    }

    /**
     * @param string      $key
     * @param string|null $functions
     *
     * @return mixed
     */
    public function w( string $key, string $functions = null ) {
        return $this->v($this->data->wildcard($key),
            $functions);
    }

    /**
     * @return string
     */
    public function __toString(): string {
        ob_start();
        include $this->file;

        return ob_get_clean();
    }

    /**
     * @param string     $name
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function __call( string $name, array $arguments ) {
        if( is_callable($name) ) {
            return call_user_func_array($name,
                $arguments);
        }

        return call_user_func_array($this->view->callRegisteredFunction($name),
            $arguments);
    }

    public function render() {
        if( $responseHandler = $this->view->getResponseHandler() ) {
            $responseHandler->header('Content-Type',
                'text/html; charset=utf-8')
                ->content($this->__toString())
                ->send();
        }
        else {
            echo $this->__toString();
        }
    }
}
