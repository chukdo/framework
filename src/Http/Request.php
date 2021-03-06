<?php

namespace Chukdo\Http;

use Chukdo\Bootstrap\App;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Str;
use Chukdo\Helper\HttpRequest;
use Chukdo\Validation\Validator;
use Chukdo\Storage\FileUploaded;
use Chukdo\Bootstrap\ServiceException;
use ReflectionException;

/**
 * Gestion de requete HTTP entrante.
 *
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
    protected App $app;

    /**
     * @param Input
     */
    protected Input $inputs;

    /**
     * @param Header
     */
    protected Header $header;

    /**
     * @param Url
     */
    protected Url $url;

    /**
     * RequestApi constructor.
     *
     * @param App $app
     *
     * @throws ReflectionException
     * @throws ServiceException
     */
    public function __construct( App $app )
    {
        $this->app    = $app;
        $this->inputs = $app->make( Input::class, true );
        $this->header = new Header();
        $this->url    = new Url( HttpRequest::uri() );
        $this->header->setHeader( 'Content-Type', (string) HttpRequest::server( 'CONTENT_TYPE' ) );
        $this->header->setHeader( 'Content-Length', (string) HttpRequest::server( 'CONTENT_LENGTH' ) );
        $this->header->setHeaders( HttpRequest::headers() );
    }

    /**
     * @param             $name
     * @param string|null $default
     *
     * @return string|null
     */
    public function server( $name, string $default = null ): ?string
    {
        return HttpRequest::server( $name, $default );
    }

    /**
     * @return Request
     */
    public function instance(): self
    {
        return $this;
    }

    /**
     * @param string      $key
     * @param string|null $default
     *
     * @return string|null
     */
    public function conf( string $key, string $default = null ): ?string
    {
        return $this->app->conf()
                         ->offsetGet( $key, $default );
    }

    /**
     * @param string      $key
     * @param string|null $default
     *
     * @return string|null
     */
    public function lang( string $key, string $default = null ): ?string
    {
        return $this->app->lang()
                         ->offsetGet( 'validation.' . $key, $default );
    }

    /**
     * @param array $rules
     *
     * @return Validator
     * @throws ReflectionException
     * @throws ServiceException
     */
    public function validate( array $rules ): Validator
    {
        $validator = $this->app->make( Validator::class );
        $validator->registerRules( $rules );
        $validator->validate();

        return $validator;
    }

    /**
     * @param string      $name
     * @param string|null $allowedMimeTypes
     * @param int|null    $maxFileSize
     *
     * @return FileUploaded|null
     */
    public function file( string $name, string $allowedMimeTypes = null, int $maxFileSize = null ): ?FileUploaded
    {
        return $this->inputs->file( $name, $allowedMimeTypes, $maxFileSize );
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
     *
     * @return mixed|null
     */
    public function input( string $name )
    {
        return $this->inputs->get( $name );
    }

    /**
     * @param mixed ...$offsets
     *
     * @return Input
     */
    public function with( ...$offsets ): Input
    {
        return new Input( $this->inputs->with( $offsets ) );
    }

    /**
     * @param mixed ...$offsets
     *
     * @return Input
     */
    public function without( ...$offsets ): Input
    {
        return new Input( $this->inputs->without( $offsets ) );
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function filled( string $path ): bool
    {
        return $this->inputs->filled( $path );
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists( string $path ): bool
    {
        return $this->inputs->exists( $path );
    }

    /**
     * @param string $path
     *
     * @return Input
     */
    public function wildcard( string $path ): Input
    {
        return new Input( $this->inputs->wildcard( $path ) );
    }

    /**
     * @return string|null
     */
    public function type(): ?string
    {
        return $this->header->getHeader( 'Content-Type' );
    }

    /**
     * @return string|null
     */
    public function length(): ?string
    {
        return $this->header->getHeader( 'Content-Length' );
    }

    /**
     * @return string|null
     */
    public function from(): ?string
    {
        $origin  = HttpRequest::server( 'HTTP_ORIGIN' );
        $referer = HttpRequest::server( 'HTTP_REFERER' );
        $remote  = HttpRequest::server( 'REMOTE_ADDR' );
        $host    = $origin
            ?: $referer
                ?: $remote;

        if ( $host !== null ) {
            $urlHost = parse_url( $host, PHP_URL_HOST );

            if ( $urlHost !== false ) {
                return $urlHost;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if ( Cli::runningInConsole() ) {
            return 'cli';
        }
        $render = Str::extension( $this->url()
                                       ->getPath() );
        if ( $render ) {
            return $render;
        }
        if ( $accept = $this->header()
                            ->getHeader( 'Accepts' ) ) {
            $renders = [
                'json' => 'json',
                'xml'  => 'xml',
                'pdf'  => 'pdf',
                'zip'  => 'zip',
            ];
            foreach ( $renders as $contain => $render ) {
                if ( Str::contain( $accept, $contain ) ) {
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
     * @return string|null
     */
    public function userAgent(): ?string
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
