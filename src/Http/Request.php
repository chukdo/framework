<?php

namespace Chukdo\Http;

use Chukdo\Bootstrap\App;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Str;
use Chukdo\Helper\HttpRequest;
use Chukdo\Json\Json;
use Chukdo\Validation\Validator;
use Chukdo\Storage\FileUploaded;

/**
 * Gestion de requete HTTP entrante.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Request
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @param Input
     */
    protected $inputs;

    /**
     * @param Header
     */
    protected $header;

    /**
     * @param Url
     */
    protected $url;

    /**
     * Request constructor.
     * @param App $app
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function __construct( App $app )
    {
        $this->app    = $app;
        $this->inputs = $app->make('Chukdo\Http\Input', true);
        $this->header = new Header();
        $this->url    = new Url(HttpRequest::uri());

        $this->header->setHeader('Content-Type',
            HttpRequest::server('CONTENT_TYPE',
                ''));
        $this->header->setHeader('Content-Length',
            HttpRequest::server('CONTENT_LENGTH',
                ''));
        $this->header->setHeaders(HttpRequest::headers());
    }

    /**
     * @param             $name
     * @param string|null $default
     * @return string|null
     */
    public function server( $name, string $default = null ): ?string
    {
        return HttpRequest::server($name, $default);
    }

    /**
     * @return Request
     */
    public function instance(): self
    {
        return $this;
    }

    /**
     * @param string $key
     * @param null   $default
     * @return string|null
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function conf( string $key, $default = null ): ?string
    {
        return $this->app->conf($key, $default);
    }

    /**
     * @param string $key
     * @param null   $default
     * @return string|null
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function lang( string $key, $default = null ): ?string
    {
        return $this->app->lang('validation.' . $key, $default);
    }

    /**
     * @param array $rules
     * @return Validator
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function validate( array $rules ): Validator
    {
        $validator = $this->app->make('Chukdo\Validation\Validator');
        $validator->registerRules($rules);
        $validator->validate();

        return $validator;
    }

    /**
     * @param string      $name
     * @param string|null $allowedMimeTypes
     * @param int|null    $maxFileSize
     * @return FileUploaded
     */
    public function file( string $name, string $allowedMimeTypes = null, int $maxFileSize = null ): FileUploaded
    {
        return $this->inputs->file($name,
            $allowedMimeTypes,
            $maxFileSize);
    }

    /**
     * @return Input
     */
    public function inputs(): Input
    {
        return $this->inputs;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function input( string $name )
    {
        return $this->inputs->get($name);
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function with( ...$offsets ): Json
    {
        return $this->inputs->with($offsets);
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function without( ...$offsets ): Json
    {
        return $this->inputs->without($offsets);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function filled( string $path ): bool
    {
        return $this->inputs->filled($path);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists( string $path ): bool
    {
        return $this->inputs->exists($path);
    }

    /**
     * @param string $path
     * @return Json
     */
    public function wildcard( string $path ): Json
    {
        return $this->inputs->wildcard($path);
    }

    /**
     * @return string|null
     */
    public function type(): ?string
    {
        return $this->header->getHeader('Content-Type');
    }

    /**
     * @return string|null
     */
    public function length(): ?string
    {
        return $this->header->getHeader('Content-Length');
    }

    /**
     * @return string|null
     */
    public function from(): ?string
    {
        return parse_url(HttpRequest::server('HTTP_ORIGIN')
            ?: HttpRequest::server('HTTP_REFERER')
                ?: HttpRequest::server('REMOTE_ADDR'),
            PHP_URL_HOST);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if ( Cli::runningInConsole() ) {
            return 'cli';
        }

        $render = Str::extension($this->url()
            ->getPath());

        if ( $render ) {
            return $render;
        }

        if ( $accept = $this->header()
            ->getHeader('Accepts') ) {
            $renders = [
                'json' => 'json',
                'xml'  => 'xml',
                'pdf'  => 'pdf',
                'zip'  => 'zip',
            ];

            foreach ( $renders as $contain => $render ) {
                if ( Str::contain($accept, $contain) ) {
                    return $render;
                }
            }
        }

        return 'html';
    }

    /**
     * @return Url
     */
    public function url(): Url
    {
        return $this->url;
    }

    /**
     * @return Header
     */
    public function header(): Header
    {
        return $this->header;
    }

    /**
     * @return bool
     */
    public function ajax(): bool
    {
        return HttpRequest::ajax();
    }

    /**
     * @return string
     */
    public function userAgent(): string
    {
        return HttpRequest::userAgent();
    }

    /**
     * @return string|null
     */
    public function method(): ?string
    {
        return HttpRequest::method();
    }

    /**
     * @return bool
     */
    public function secured(): bool
    {
        return HttpRequest::secured();
    }
}
