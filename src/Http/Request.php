<?php

namespace Chukdo\Http;

use Chukdo\Bootstrap\App;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Str;
use Chukdo\Helper\Http;
use Chukdo\Json\Json;
use Chukdo\Json\Input;
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
     * @param string
     */
    protected $method;

    /**
     * Request constructor.
     * @param App $app
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function __construct( App $app )
    {
        $this->app    = $app;
        $this->inputs = $app->make('Chukdo\Json\Input', true);
        $this->header = new Header();
        $this->url    = new Url(Http::uri());
        $this->method = Http::method();

        $this->header->setHeader('Content-Type',
            Http::server('CONTENT_TYPE',
                ''));
        $this->header->setHeader('Content-Length',
            Http::server('CONTENT_LENGTH',
                ''));
        $this->header->setHeaders(Http::headers());
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
    public function only( ...$offsets ): Json
    {
        return $this->inputs->only($offsets);
    }

    /**
     * @param mixed ...$offsets
     * @return Json
     */
    public function except( ...$offsets ): Json
    {
        return $this->inputs->except($offsets);
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
        return parse_url(Http::server('HTTP_ORIGIN')
            ?: Http::server('HTTP_REFERER')
                ?: Http::server('REMOTE_ADDR'),
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
        return $this->header->getHeader('X-Requested-with') === 'XMLHttpRequest';
    }

    /**
     * @return string|null
     */
    public function method(): ?string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function userAgent(): array
    {
        return Http::getUserAgent(Http::server('HTTP_USER_AGENT'));
    }

    /**
     * @return bool
     */
    public function secured(): bool
    {
        return Http::server('HTTPS') || Http::server('SERVER_PORT') == '443'
               || Http::server('REQUEST_SCHEME') == 'https';
    }
}
